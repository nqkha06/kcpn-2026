Feature('User Budgets - Create');

const USER_EMAIL = process.env.USER_EMAIL || 'user@example.com';
const USER_PASSWORD = process.env.USER_PASSWORD || 'password';

Before(({ I }) => {
    I.amOnPage('/login');
    I.fillField('#email', USER_EMAIL);
    I.fillField('#password', USER_PASSWORD);
    I.click('Log in');
    I.amOnPage('/budgets');
});

Scenario('Open create budget dialog', ({ I }) => {
    I.click('Tạo ngân sách');
    I.see('Tạo ngân sách', '[role="dialog"]');
    I.see('Danh mục');
    I.see('Hạn mức');
    I.see('Chu kỳ');
    I.see('Ghi chú');
    I.see('Lưu ngân sách');
    I.see('Hủy');
});

Scenario('Create a budget successfully', ({ I }) => {
    I.click('Tạo ngân sách');
    I.see('Tạo ngân sách', '[role="dialog"]');

    within('[role="dialog"]', () => {
        I.selectOption('select[name="category_id"]', { index: 1 });
        I.fillField('input[name="amount_limit"]', '1000000');
        I.selectOption('select[name="period"]', 'monthly');
        I.fillField('textarea[name="note"]', 'Ngân sách E2E');
        I.click('Lưu ngân sách');
    });

    I.see('Đã tạo ngân sách.');
    I.dontSee('Tạo ngân sách', '[role="dialog"]');
});

Scenario('Cancel closes the dialog without creating a budget', ({ I }) => {
    I.click('Tạo ngân sách');
    I.see('Tạo ngân sách', '[role="dialog"]');
    I.click('Hủy');
    I.dontSeeElement('[role="dialog"]');
});

Scenario('Validate amount limit on create', ({ I }) => {
    I.click('Tạo ngân sách');
    I.see('Tạo ngân sách', '[role="dialog"]');

    within('[role="dialog"]', () => {
        I.fillField('input[name="amount_limit"]', '0');
        I.click('Lưu ngân sách');
    });

    I.see('Hạn mức phải lớn hơn 0.');
});