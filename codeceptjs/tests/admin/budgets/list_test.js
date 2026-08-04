Feature('Admin Budgets - List');

const ADMIN_EMAIL = process.env.ADMIN_EMAIL || 'admin@example.com';
const ADMIN_PASSWORD = process.env.ADMIN_PASSWORD || 'password';

Before(({ I }) => {
    I.amOnPage('/login');
    I.fillField('#email', ADMIN_EMAIL);
    I.fillField('#password', ADMIN_PASSWORD);
    I.click('Log in');
    I.amOnPage('/admin/budgets');
    I.seeElement('body');
});

Scenario('Display budget list', ({ I }) => {
    I.see('Budgets');
    I.see('Manage monthly and yearly budgets for users.');
    I.see('Add Budget');
    I.seeElement('table');
    I.see('ID');
    I.see('User');
    I.see('Category');
    I.see('Limit');
    I.see('Spent');
    I.see('Period');
    I.see('Status');
});

Scenario('Verify period column', ({ I }) => {
    I.seeElement('table');
    I.see('Period');
    within('table thead tr', () => {
        I.see('Period');
    });
});

Scenario('Verify status column', ({ I }) => {
    I.seeElement('table');
    I.see('Status');
    within('table thead tr', () => {
        I.see('Status');
    });
    I.see('Active');
});

Scenario('Filter budgets by period and status', ({ I }) => {
    I.click('Filters');
    I.see('Period');
    I.see('Status');
    I.see('User');
    I.see('Category');
});