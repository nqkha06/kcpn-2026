Feature('Admin Budgets - Delete');

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
    I.fillField('#amount_limit', '200.00');
    I.selectOption('#period', 'monthly');
    I.selectOption('#status', 'active');
    I.fillField('#note', 'Delete test seed budget');
    I.click('Create Budget');
    I.see('Budget created successfully');
    I.seeInCurrentUrl('/admin/budgets');
});

Scenario('Open confirm delete dialog with correct title', ({ I }) => {
    I.amOnPage('/admin/budgets');
    I.seeElement('table');

    within(locate('table tbody tr').at(1), () => {
        I.click('[aria-label="Delete"]');
    });

    I.see('Delete Budget');
    I.see('This action cannot be undone.');
});

Scenario('Cancel delete keeps budget in the table', ({ I }) => {
    I.amOnPage('/admin/budgets');
    I.seeElement('table');

    within(locate('table tbody tr').at(1), () => {
        I.click('[aria-label="Delete"]');
    });

    I.see('Delete Budget');
    I.click('Cancel');
    I.dontSee('Delete Budget');
    I.seeElement('table');
});

Scenario('Confirm delete removes budget and shows success message', ({ I }) => {
    I.amOnPage('/admin/budgets');
    I.seeElement('table');

    within(locate('table tbody tr').at(1), () => {
        I.click('[aria-label="Delete"]');
    });

    I.see('Delete Budget');
    I.click('Confirm');

    I.see('Budget deleted successfully');
    I.seeElement('table');
});