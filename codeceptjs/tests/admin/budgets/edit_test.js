Feature('Admin Budgets - Edit');

const ADMIN_EMAIL = process.env.ADMIN_EMAIL || 'admin@example.com';
const ADMIN_PASSWORD = process.env.ADMIN_PASSWORD || 'password';

Before(({ I }) => {
    I.amOnPage('/login');
    I.fillField('#email', ADMIN_EMAIL);
    I.fillField('#password', ADMIN_PASSWORD);
    I.click('Log in');

    I.amOnPage('/admin/budgets/create');
    I.selectOption('#user_id', { index: 1 });
    I.selectOption('#category_id', { index: 1 });
    I.fillField('#amount_limit', '300.00');
    I.selectOption('#period', 'monthly');
    I.selectOption('#status', 'active');
    I.fillField('#note', 'Edit test seed budget');
    I.click('Create Budget');
    I.see('Budget created successfully');
    I.seeInCurrentUrl('/admin/budgets');
});

Scenario('Open edit form from list and verify prefilled values', ({ I }) => {
    I.amOnPage('/admin/budgets');
    I.seeElement('table');

    within(locate('table tbody tr').at(1), () => {
        I.click('[aria-label="Edit"]');
    });

    I.see('Edit Budget');
    I.see('Update budget details and status.');
    I.seeElement('#user_id');
    I.seeElement('#category_id');
    I.seeInField('#period', 'monthly');
    I.seeInField('#status', 'active');
});

Scenario('Update budget fields and verify success message', ({ I }) => {
    I.amOnPage('/admin/budgets');
    I.seeElement('table');

    within(locate('table tbody tr').at(1), () => {
        I.click('[aria-label="Edit"]');
    });

    I.see('Edit Budget');
    I.fillField('#amount_limit', '750.00');
    I.selectOption('#period', 'yearly');
    I.selectOption('#status', 'inactive');
    I.fillField('#note', 'Updated via E2E');

    I.click('Save Changes');

    I.see('Budget updated successfully');
    I.seeInCurrentUrl('/admin/budgets');
});

Scenario('Verify updated budget appears in the table', ({ I }) => {
    I.amOnPage('/admin/budgets');
    I.seeElement('table');
    within('table', () => {
        I.see('yearly');
        I.see('inactive');
    });
});