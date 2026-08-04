Feature('Admin Menus - Delete');

const ADMIN_EMAIL = process.env.ADMIN_EMAIL || 'admin@example.com';
const ADMIN_PASSWORD = process.env.ADMIN_PASSWORD || 'password';

const menuTitle = `Delete Menu E2E ${Date.now()}`;

Before(({ I }) => {
    I.amOnPage('/login');
    I.fillField('#email', ADMIN_EMAIL);
    I.fillField('#password', ADMIN_PASSWORD);
    I.click('Log in');

    I.amOnPage('/admin/menus/create');
    I.fillField('#title', menuTitle);
    I.fillField('#url', '/delete-menu-e2e');
    I.fillField('#canonical', 'home.header');
    I.fillField('#sort_order', '0');
    I.selectOption('#target', '_self');
    I.selectOption('#status', 'active');
    I.click('Save');
    I.see('Menu created successfully');
    I.seeInCurrentUrl('/admin/menus');
    I.see(menuTitle);
});

Scenario('Open confirm delete dialog with correct title and description', ({ I }) => {
    I.amOnPage('/admin/menus');
    I.see(menuTitle);

    within(locate('table tbody tr').withText(menuTitle), () => {
        I.click('[aria-label="Delete"]');
    });

    I.see('Delete Menu');
    I.see(`Are you sure you want to delete ${menuTitle}? This action cannot be undone.`);
});

Scenario('Cancel delete keeps menu in the table', ({ I }) => {
    I.amOnPage('/admin/menus');
    I.see(menuTitle);

    within(locate('table tbody tr').withText(menuTitle), () => {
        I.click('[aria-label="Delete"]');
    });

    I.see('Delete Menu');
    I.click('Cancel');
    I.dontSee('Delete Menu');
    I.see(menuTitle);
});

Scenario('Confirm delete removes menu and shows success message', ({ I }) => {
    I.amOnPage('/admin/menus');
    I.see(menuTitle);

    within(locate('table tbody tr').withText(menuTitle), () => {
        I.click('[aria-label="Delete"]');
    });

    I.see('Delete Menu');
    I.click('Confirm');

    I.see('Menu deleted successfully');
    I.seeElement('table');
    I.dontSee(menuTitle);
});