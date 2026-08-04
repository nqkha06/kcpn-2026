Feature('Admin Menus - Edit');

const ADMIN_EMAIL = process.env.ADMIN_EMAIL || 'admin@example.com';
const ADMIN_PASSWORD = process.env.ADMIN_PASSWORD || 'password';

const originalTitle = `Edit Menu E2E ${Date.now()}`;
const updatedTitle = `Edit Menu E2E Updated ${Date.now()}`;

Before(({ I }) => {
    I.amOnPage('/login');
    I.fillField('#email', ADMIN_EMAIL);
    I.fillField('#password', ADMIN_PASSWORD);
    I.click('Log in');

    I.amOnPage('/admin/menus/create');
    I.fillField('#title', originalTitle);
    I.fillField('#url', '/edit-menu-e2e');
    I.fillField('#canonical', 'home.header');
    I.fillField('#sort_order', '0');
    I.selectOption('#target', '_self');
    I.selectOption('#status', 'active');
    I.click('Save');
    I.see('Menu created successfully');
    I.seeInCurrentUrl('/admin/menus');
    I.see(originalTitle);
});

Scenario('Open edit form from list and verify prefilled values', ({ I }) => {
    I.amOnPage('/admin/menus');
    I.seeElement('table');
    I.see(originalTitle);

    within(locate('table tbody tr').withText(originalTitle), () => {
        I.click('[aria-label="Edit"]');
    });

    I.see('Edit Menu');
    I.seeInField('#title', originalTitle);
    I.seeInField('#url', '/edit-menu-e2e');
    I.seeInField('#canonical', 'home.header');
    I.seeInField('#sort_order', '0');
    I.seeInField('#status', 'active');
});

Scenario('Update menu fields and verify success message', ({ I }) => {
    I.amOnPage('/admin/menus');
    I.see(originalTitle);

    within(locate('table tbody tr').withText(originalTitle), () => {
        I.click('[aria-label="Edit"]');
    });

    I.see('Edit Menu');
    I.fillField('#title', updatedTitle);
    I.fillField('#url', '/edit-menu-e2e-updated');
    I.fillField('#sort_order', '5');
    I.selectOption('#target', '_blank');
    I.selectOption('#status', 'inactive');

    I.click('Save');

    I.see('Menu updated successfully');
    I.seeInCurrentUrl('/admin/menus');
});

Scenario('Verify updated menu appears in the table', ({ I }) => {
    I.amOnPage('/admin/menus');
    I.seeElement('table');
    I.see(updatedTitle);
    I.dontSee(originalTitle);

    within(locate('table tbody tr').withText(updatedTitle), () => {
        I.see('inactive');
    });
});