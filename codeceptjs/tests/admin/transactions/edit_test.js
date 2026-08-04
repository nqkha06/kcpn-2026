Feature('Admin transactions - Edit');

Before(({ I }) => I.loginAsAdmin());

Scenario('loads and saves an existing transaction', ({ I }) => {
    I.openAdminPage('/admin/transactions', 'Transactions');
    I.clickFirstTableAction('Edit');
    I.waitForPathEnding('/edit');
    I.waitForText('Edit Transaction', 10, 'h2');
    I.waitForFieldValue('#user_id');
    I.waitForFieldValue('#wallet_id');
    I.seeElement('#amount:not([value=""])');
    I.click('Save Changes');
    I.seeToast('Transaction updated successfully');
});
