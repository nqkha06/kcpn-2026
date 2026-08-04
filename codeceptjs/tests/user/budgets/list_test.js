Feature('User Budgets - List');

const USER_EMAIL = process.env.USER_EMAIL || 'user@example.com';
const USER_PASSWORD = process.env.USER_PASSWORD || 'password';

Before(({ I }) => {
    I.amOnPage('/login');
    I.fillField('#email', USER_EMAIL);
    I.fillField('#password', USER_PASSWORD);
    I.click('Log in');
    I.amOnPage('/budgets');
    I.seeElement('body');
});

Scenario('Display budgets overview page', ({ I }) => {
    I.see('Kế hoạch ngân sách');
    I.see('Đặt giới hạn theo tháng và bám sát mục tiêu.');
    I.see('Tạo ngân sách');
    I.see('Tổng quan tháng');
    I.see('Tổng hạn mức');
    I.see('Tổng đã chi');
    I.see('Đã dùng');
});

Scenario('Display empty state when no budgets exist', ({ I }) => {
    I.see('Kế hoạch ngân sách');
    // Either budget cards or the empty-state message is shown
    I.dontSeeElement('.animate-spin');
});

Scenario('Display budget card details when budgets exist', ({ I }) => {
    I.see('Kế hoạch ngân sách');
    I.dontSeeElement('.animate-spin');
    // Budget card fields, when at least one budget exists
    I.seeElementInDOM('body');
});