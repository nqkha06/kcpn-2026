Feature('Admin Menus - Create');

const ADMIN_EMAIL = process.env.ADMIN_EMAIL || 'admin@example.com';
const ADMIN_PASSWORD = process.env.ADMIN_PASSWORD || 'password';

const parentTitle = `Parent Menu E2E ${Date.now()}`;
const childTitle = `Child Menu E2E ${Date.now()}`;

Before(({ I }) => {
    I.amOnPage('/login');
    I.fillField('#email', ADMIN_EMAIL);
    I.fillField('#password', ADMIN_PASSWORD);
    I.click('Log in');
});

Scenario('Create parent menu', ({ I }) => {
    I.amOnPage('/admin/menus/create');
    I.see('Create Menu');

    I.fillField('#title', parentTitle);
    I.fillField('#url', '/parent-menu-e2e');
    I.fillField('#canonical', 'home.header');
    I.fillField('#sort_order', '0');
    I.selectOption('#target', '_self');
    I.selectOption('#status', 'active');

    I.click('Save');

    I.see('Menu created successfully');

    I.seeInCurrentUrl('/admin/menus');
    I.seeElement('table');
    I.see(parentTitle);
});

Scenario('Create child menu and verify parent options', ({ I }) => {
    I.amOnPage('/admin/menus/create');
    I.see('Create Menu');

    I.fillField('#title', childTitle);
    I.fillField('#url', '/child-menu-e2e');
    I.fillField('#canonical', 'home.header');

    I.seeElement('#parent_id');
    I.see('No parent', '#parent_id');
    I.see(parentTitle, '#parent_id');

    I.selectOption('#parent_id', parentTitle);
    I.fillField('#sort_order', '1');
    I.selectOption('#target', '_self');
    I.selectOption('#status', 'active');

    I.click('Save');

    I.see('Menu created successfully');

    I.seeInCurrentUrl('/admin/menus');
    I.seeElement('table');
    I.see(childTitle);
    within('table', () => {
        I.see(parentTitle);
    });
});