Feature('User Categories - Create');

const userOne = {
    email: process.env.TEST_USER_EMAIL || 'user1@example.com',
    password: process.env.TEST_USER_PASSWORD || 'password',
};

function login(I, { email, password }) {
    I.amOnPage('/login');
    I.fillField('#email', email);
    I.fillField('#password', password);
    I.click('Log in');
    I.waitInUrl('/dashboard');
}

Before(({ I }) => {
    login(I, userOne);
    I.amOnPage('/categories');
});

Scenario('creates a private category successfully', ({ I }) => {
    const categoryName = `Danh mục riêng ${Date.now()}`;

    I.click('Thêm danh mục');
    I.see('Thêm danh mục riêng', { css: '[role="dialog"]' });
    I.fillField('name', categoryName);
    I.fillField('color', '#0EA5E9');
    I.fillField('description', 'Mô tả danh mục riêng dùng để kiểm thử.');
    I.click('Tạo danh mục');

    I.see('Đã tạo danh mục riêng.');
    I.dontSeeElement({ css: '[role="dialog"]' });
    I.see(categoryName);
});

Scenario('validates that name is required', ({ I }) => {
    I.click('Thêm danh mục');
    I.see('Thêm danh mục riêng', { css: '[role="dialog"]' });
    I.fillField('color', '#0EA5E9');
    I.click('Tạo danh mục');

    I.see('Vui lòng nhập tên danh mục.');
    I.seeElement({ css: '[role="dialog"]' });
});

Scenario('validates that color is required', ({ I }) => {
    I.click('Thêm danh mục');
    I.see('Thêm danh mục riêng', { css: '[role="dialog"]' });
    I.fillField('name', 'Danh mục thiếu màu');
    I.clearField('color');
    I.click('Tạo danh mục');

    I.see('Vui lòng chọn mã màu hợp lệ.');
    I.seeElement({ css: '[role="dialog"]' });
});