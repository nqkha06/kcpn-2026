// tests/admin/menus/list_test.js
Feature('Admin Menus - List');

const ADMIN_EMAIL = process.env.ADMIN_EMAIL || 'admin@example.com';
const ADMIN_PASSWORD = process.env.ADMIN_PASSWORD || 'password';

Before(({ I }) => {
    I.amOnPage('/login');
    I.fillField('#email', ADMIN_EMAIL);
    I.fillField('#password', ADMIN_PASSWORD);
    I.click('Log in');
    I.amOnPage('/admin/menus');
    I.seeElement('body');
});

Scenario('Display menu list', ({ I }) => {
    I.see('Menus');
    I.see('Manage dynamic navigation menus by canonical slot.');
    I.see('Add Menu');
    I.seeElement('table');
    I.see('Title');
    I.see('URL');
    I.see('Canonical');
    I.see('Parent');
    I.see('Order');
    I.see('Status');
    I.see('Created At');
});

Scenario('Verify canonical column', ({ I }) => {
    I.seeElement('table');
    I.see('Canonical');
    within('table thead tr', () => {
        I.see('Canonical');
    });
    within('table tbody tr:first-child', () => {
        I.seeElement('.rounded-xl, table td');
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

Scenario.todo('Verify target column (not present in Menus list table - see MenuFormView "Target" field instead)');