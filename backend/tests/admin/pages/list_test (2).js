Feature('Admin pages - Create');

Before(({ I }) => I.loginAsAdmin());

Scenario('validates and creates a published page', ({ I }) => {
    const suffix = Date.now();
    const title = `E2E Page ${suffix}`;
    const slug = `e2e-page-${suffix}`;

    I.openAdminPage('/admin/pages/create', 'Create Page');
    I.click('Save');
    I.see('Title is required.');
    I.fillField('#title', title);
    I.fillField('#slug', slug);
    I.fillField('#meta_description', 'A page created by CodeceptJS.');
    I.selectOption('select[name="status"]', 'Published');
    I.click('Save');
    I.seeToast('Page created successfully');
    I.waitForText(title, 10, 'tbody');
});
