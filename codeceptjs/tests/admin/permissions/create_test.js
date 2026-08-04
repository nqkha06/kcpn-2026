Feature('Admin permissions - Create');

Before(({ I }) => I.loginAsAdmin());

Scenario('validates and creates a permission', ({ I }) => {
    const name = `e2e permission ${Date.now()}`;

    I.openAdminPage('/admin/permissions/create', 'Create Permission');
    I.click('Create Permission');
    I.see('The permission name field is required.');
    I.fillField('#name', name);
    I.click('Create Permission');
    I.seeToast('Permission created successfully');
    I.waitForText(name, 10, 'tbody');
});
