Feature('Admin transactions - Create');

Before(({ I }) => I.loginAsAdmin());

Scenario('validates required transaction fields', ({ I }) => {
    I.openAdminPage('/admin/transactions/create', 'Create Transaction');
    I.click('Create Transaction');
    I.see('Please select a user.');
    I.see('Please select a wallet.');
    I.see('Amount must be greater than zero.');
});

Scenario('creates an expense transaction', ({ I }) => {
    I.openAdminPage('/admin/transactions/create', 'Create Transaction');
    I.selectOptionContaining('#user_id', process.env.USER_EMAIL);
    I.waitForElement('#wallet_id option:not([value=""])', 10);
    I.selectFirstOption('#wallet_id');
    I.fillField('#amount', '76543.21');
    I.fillField('#note', `CodeceptJS transaction ${Date.now()}`);
    I.fillField('#labels', 'e2e, admin');
    I.click('Create Transaction');
    I.seeToast('Transaction created successfully');
    I.waitForText('-76,543.21', 10, 'tbody');
});
