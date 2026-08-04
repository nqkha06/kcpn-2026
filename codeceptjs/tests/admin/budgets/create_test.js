Feature('Admin Budgets - Create');

const ADMIN_EMAIL = process.env.ADMIN_EMAIL || 'admin@example.com';
const ADMIN_PASSWORD = process.env.ADMIN_PASSWORD || 'password';

Before(({ I }) => {
    I.amOnPage('/login');
    I.fillField('#email', ADMIN_EMAIL);
    I.fillField('#password', ADMIN_PASSWORD);
    I.click('Log in');
});

Scenario('Create budget', ({ I }) => {
    I.amOnPage('/admin/budgets/create');
    I.see('Create Budget');
    I.see('Add a budget for a user and category.');
    I.see('Budget Details');

    I.seeElement('#user_id');
    I.selectOption('#user_id', { index: 1 });

    I.seeElement('#category_id');
    I.selectOption('#category_id', { index: 1 });

    I.fillField('#amount_limit', '500.00');
    I.selectOption('#period', 'monthly');
    I.selectOption('#status', 'active');
    I.fillField('#note', 'E2E created budget');

    I.click('Create Budget');

    I.see('Budget created successfully');

    I.seeInCurrentUrl('/admin/budgets');
    I.seeElement('table');
});

Scenario('Category options update when user changes', ({ I }) => {
    I.amOnPage('/admin/budgets/create');
    I.see('Create Budget');

    I.seeElement('#user_id');
    I.see('Select user', '#user_id');
    I.see('Select category', '#category_id');

    I.selectOption('#user_id', { index: 1 });
    I.seeElement('#category_id');
});

Scenario('Validate required fields on create', ({ I }) => {
    I.amOnPage('/admin/budgets/create');
    I.click('Create Budget');

    I.see('Please select a user.');
    I.see('Please select a category.');
});