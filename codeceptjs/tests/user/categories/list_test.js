Feature('User Categories - List');

const userOne = {
    email: process.env.TEST_USER_EMAIL || 'user1@example.com',
    password: process.env.TEST_USER_PASSWORD || 'password',
};

const userTwo = {
    email: process.env.TEST_USER2_EMAIL || 'user2@example.com',
    password: process.env.TEST_USER2_PASSWORD || 'password',
};

function login(I, { email, password }) {
    I.amOnPage('/login');
    I.fillField('#email', email);
    I.fillField('#password', password);
    I.click('Log in');
    I.waitInUrl('/dashboard');
}

Scenario('displays global categories and the current user\'s private categories', ({ I }) => {
    login(I, userOne);
    I.amOnPage('/categories');
    I.see('Danh mục riêng');
    I.see('Không chia sẻ với người dùng khác');

    I.click('Thêm danh mục');
    I.see('Thêm danh mục riêng', { css: '[role="dialog"]' });
    I.fillField('name', 'Danh mục global test');
    I.fillField('color', '#0EA5E9');
    I.click('Tạo danh mục');
    I.see('Đã tạo danh mục riêng.');
    I.dontSeeElement({ css: '[role="dialog"]' });

    I.see('Danh mục global test');
});

Scenario('does not display private categories belonging to other users', ({ I }) => {
    const privateCategoryName = `Danh mục riêng của user1 ${Date.now()}`;

    login(I, userOne);
    I.amOnPage('/categories');
    I.click('Thêm danh mục');
    I.fillField('name', privateCategoryName);
    I.fillField('color', '#0EA5E9');
    I.click('Tạo danh mục');
    I.see('Đã tạo danh mục riêng.');
    I.see(privateCategoryName);

    I.amOnPage('/login');
    login(I, userTwo);
    I.amOnPage('/categories');
    I.see('Danh mục riêng');
    I.dontSee(privateCategoryName);
});