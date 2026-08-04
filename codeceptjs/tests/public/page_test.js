Feature('Public site - Published pages');

Scenario('renders a published page created for the test', ({ I }) => {
    const suffix = Date.now();
    const title = `E2E Public Page ${suffix}`;
    const slug = `e2e-public-page-${suffix}`;

    I.loginAsAdmin();
    I.openAdminPage('/admin/pages/create', 'Create Page');
    I.fillField('#title', title);
    I.fillField('#slug', slug);
    I.fillField('[role="textbox"][contenteditable="true"]', 'Public content created by CodeceptJS.');
    I.selectOption('select[name="status"]', 'Published');
    I.click('Save');
    I.seeToast('Page created successfully');

    I.amOnPage(`/p/${slug}`);
    I.waitForText(title, 10, 'h1');
    I.seeElement('article');

    I.openAdminPage('/admin/pages', 'Pages');
    I.clickRowAction(title, 'Delete');
    I.waitForText('Delete Page', 5, 'h2');
    I.click({ xpath: "//button[normalize-space()='Delete']" });
    I.seeToast('Page deleted successfully');
});

Scenario('returns not found for an unknown page slug', ({ I }) => {
    I.amOnPage(`/p/e2e-page-does-not-exist-${Date.now()}`);
    I.waitForText('404', 10);
});
