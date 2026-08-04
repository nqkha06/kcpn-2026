Feature('User Categories - Delete');

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

Scenario('deletes an unused private category after confirmation', ({ I }) => {
    const categoryName = `Danh mục xóa ${Date.now()}`;

    I.click('Thêm danh mục');
    I.see('Thêm danh mục riêng', { css: '[role="dialog"]' });
    I.fillField('name', categoryName);
    I.fillField('color', '#0EA5E9');
    I.click('Tạo danh mục');
    I.see('Đã tạo danh mục riêng.');
    I.see(categoryName);

    I.click(`Xóa ${categoryName}`);
    I.see(`Xóa danh mục "${categoryName}"?`, { css: '[role="dialog"]' });
    I.see('Chỉ có thể xóa danh mục chưa được dùng trong giao dịch hoặc ngân sách.');
    I.see('Xóa danh mục', { css: '[role="dialog"]' });
    I.see('Hủy', { css: '[role="dialog"]' });

    I.click('Xóa danh mục');
    I.see('Đã xóa danh mục riêng.');
    I.dontSeeElement({ css: '[role="dialog"]' });
    I.dontSee(categoryName);
});

Scenario('shows the confirmation dialog and allows cancelling', ({ I }) => {
    const categoryName = `Danh mục hủy xóa ${Date.now()}`;

    I.click('Thêm danh mục');
    I.fillField('name', categoryName);
    I.fillField('color', '#0EA5E9');
    I.click('Tạo danh mục');
    I.see('Đã tạo danh mục riêng.');
    I.see(categoryName);

    I.click(`Xóa ${categoryName}`);
    I.see(`Xóa danh mục "${categoryName}"?`, { css: '[role="dialog"]' });
    I.click('Hủy');
    I.dontSeeElement({ css: '[role="dialog"]' });
    I.see(categoryName);
});

Scenario('shows an error when deleting a category used by a transaction', ({ I }) => {
    const categoryName = process.env.TEST_CATEGORY_USED_BY_TRANSACTION || 'Danh mục đang dùng trong giao dịch';

    I.see(categoryName);
    I.click(`Xóa ${categoryName}`);
    I.see(`Xóa danh mục "${categoryName}"?`, { css: '[role="dialog"]' });
    I.click('Xóa danh mục');

    I.seeElement({ css: '[role="dialog"]' });
    I.see(`Xóa danh mục "${categoryName}"?`, { css: '[role="dialog"]' });
    I.see(categoryName);
});

Scenario('shows an error when deleting a category used by a budget', ({ I }) => {
    const categoryName = process.env.TEST_CATEGORY_USED_BY_BUDGET || 'Danh mục đang dùng trong ngân sách';

    I.see(categoryName);
    I.click(`Xóa ${categoryName}`);
    I.see(`Xóa danh mục "${categoryName}"?`, { css: '[role="dialog"]' });
    I.click('Xóa danh mục');

    I.seeElement({ css: '[role="dialog"]' });
    I.see(`Xóa danh mục "${categoryName}"?`, { css: '[role="dialog"]' });
    I.see(categoryName);
});