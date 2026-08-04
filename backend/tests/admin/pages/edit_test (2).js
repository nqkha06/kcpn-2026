Feature('Admin pages - Delete');

Before(({ I }) => I.loginAsAdmin());

Scenario('deletes a page created for the test', ({ I }) => {
    const suffix = Date.now();
    const title = `E2E Delete Page ${suffix}`;

    I.openAdminPage('/admin/pages/create', 'Create Page');
    I.fillField('#title', title);
    I.fillField('#slug', `e2e-delete-page-${suffix}`);
    I.click('Save');
    I.seeToast('Page created successfully');
    I.clickRowAction(title, 'Delete');
    I.waitForText('Delete Page', 5, 'h2');
    I.click({ xpath: "//button[normalize-space()='Delete']" });
    I.seeToast('Page deleted successfully');
    I.dontSee(title, 'tbody');
});
