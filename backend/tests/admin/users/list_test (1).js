Feature('Admin users - Create');

Before(({ I }) => I.loginAsAdmin());

Scenario('validates required fields', ({ I }) => {
    I.openAdminPage('/admin/users/create', 'Create User');
    I.click('Create User');
    I.see('The name field is required.');
    I.see('The email field is required.');
    I.see('The password field is required.');
});

Scenario('creates a user', ({ I }) => {
    const suffix = `${Date.now()}${Math.floor(Math.random() * 1000)}`;
    const name = `E2E Admin User ${suffix}`;

    I.openAdminPage('/admin/users/create', 'Create User');
    I.fillField('#name', name);
    I.fillField('#email', `admin-created-${suffix}@example.com`);
    I.fillField('#password', 'Password123!');
    I.fillField('#password_confirmation', 'Password123!');
    I.click('Create User');
    I.seeToast('User created successfully');
    I.waitForText(name, 10, 'tbody');
});
