Feature('Admin users - Delete');

Before(({ I }) => I.loginAsAdmin());

Scenario('deletes a user created for the test', ({ I }) => {
    const suffix = `${Date.now()}${Math.floor(Math.random() * 1000)}`;
    const name = `E2E Delete User ${suffix}`;

    I.openAdminPage('/admin/users/create', 'Create User');
    I.fillField('#name', name);
    I.fillField('#email', `delete-user-${suffix}@example.com`);
    I.fillField('#password', 'Password123!');
    I.fillField('#password_confirmation', 'Password123!');
    I.click('Create User');
    I.seeToast('User created successfully');
    I.clickRowAction(name, 'Delete');
    I.waitForText('Delete User', 5, 'h2');
    I.click({ xpath: "//button[normalize-space()='Delete']" });
    I.seeToast('User deleted successfully');
    I.dontSee(name, 'tbody');
});
