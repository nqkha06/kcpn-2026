Feature('Admin permissions - Delete');

Before(({ I }) => I.loginAsAdmin());

Scenario('deletes a permission created for the test', ({ I }) => {
    const name = `e2e delete permission ${Date.now()}`;

    I.openAdminPage('/admin/permissions/create', 'Create Permission');
    I.fillField('#name', name);
    I.click('Create Permission');
    I.seeToast('Permission created successfully');
    I.clickRowAction(name, 'Delete');
    I.waitForText('Delete Permission', 5, 'h2');
    I.click({ xpath: "//button[normalize-space()='Delete']" });
    I.seeToast('Permission deleted successfully');
    I.dontSee(name, 'tbody');
});
