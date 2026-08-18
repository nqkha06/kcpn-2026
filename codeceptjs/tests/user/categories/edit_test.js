const assert = require('node:assert/strict');
const { loadTestData } = require('../../../support/test-data');

Feature('User Categories - Edit shared data');

for (const testCase of loadTestData('user/categories/update.json')) {
    Scenario(`[${testCase.case_id}] ${testCase.description}`, () => {
        assert.equal(testCase.request.method, 'PUT');
        assert.equal(testCase.request.endpoint, '/api/v1/user/categories/{category}');
        assert.ok(Number.isInteger(testCase.expected.status));
    }).tag('@shared-data');
}

Feature('User Categories - Edit');

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

Scenario('updates a private category and persists changes after reload', ({ I }) => {
    const originalName = `Danh mục sửa ${Date.now()}`;
    const updatedName = `Danh mục đã sửa ${Date.now()}`;

    I.click('Thêm danh mục');
    I.see('Thêm danh mục riêng', { css: '[role="dialog"]' });
    I.fillField('name', originalName);
    I.fillField('color', '#0EA5E9');
    I.click('Tạo danh mục');
    I.see('Đã tạo danh mục riêng.');
    I.see(originalName);

    I.click(`Sửa ${originalName}`);
    I.see('Sửa danh mục riêng', { css: '[role="dialog"]' });
    I.fillField('name', updatedName);
    I.fillField('color', '#F97316');
    I.click('Lưu thay đổi');
    I.see('Đã cập nhật danh mục riêng.');
    I.dontSeeElement({ css: '[role="dialog"]' });
    I.see(updatedName);
    I.dontSee(originalName);

    I.amOnPage('/categories');
    I.see(updatedName);
    I.dontSee(originalName);
});

Scenario('prevents editing a global category', ({ I }) => {
    I.see('Danh mục riêng');
    I.see('Không chia sẻ với người dùng khác');

    I.dontSeeElement({ css: 'button[aria-label^="Sửa Ăn uống"]' });
    I.dontSeeElement({ css: 'button[aria-label^="Xóa Ăn uống"]' });
    I.dontSeeElement({ css: 'button[aria-label^="Sửa Di chuyển"]' });
    I.dontSeeElement({ css: 'button[aria-label^="Xóa Di chuyển"]' });
});
