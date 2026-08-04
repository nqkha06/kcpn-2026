Feature('Admin categories - Create');

Before(({ I }) => I.loginAsAdmin());

Scenario('validates and creates a category', ({ I }) => {
    const name = `E2E Category ${Date.now()}`;

    I.openAdminPage('/admin/categories/create', 'Create Category');
    I.click('Create Category');
    I.see('Please provide a category name.');
    I.fillField('#name', name);
    I.fillField('#description', 'Created by CodeceptJS');
    I.selectOption('#status', 'Active');
    I.click('Create Category');
    I.seeToast('Category created successfully');
    I.waitForText(name, 10, 'tbody');
});
