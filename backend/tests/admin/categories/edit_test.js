Feature('Admin categories - Delete');

Before(({ I }) => I.loginAsAdmin());

Scenario('deletes a category created for the test', ({ I }) => {
    const name = `E2E Delete Category ${Date.now()}`;

    I.openAdminPage('/admin/categories/create', 'Create Category');
    I.fillField('#name', name);
    I.click('Create Category');
    I.seeToast('Category created successfully');
    I.clickRowAction(name, 'Delete');
    I.waitForText('Delete Category', 5, 'h2');
    I.click({ xpath: "//button[normalize-space()='Delete']" });
    I.seeToast('Category deleted successfully');
    I.dontSee(name, 'tbody');
});
