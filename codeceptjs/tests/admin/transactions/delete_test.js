Feature('Admin transactions - Delete');

Before(({ I }) => I.loginAsAdmin());

Scenario('deletes a transaction created for the test', ({ I }) => {
    const displayedAmount = '-87,654.32';

    I.openAdminPage('/admin/transactions/create', 'Create Transaction');
    I.selectOptionContaining('#user_id', process.env.USER_EMAIL);
    I.waitForElement('#wallet_id option:not([value=""])', 10);
    I.selectFirstOption('#wallet_id');
    I.fillField('#amount', '87654.32');
    I.fillField('#note', `CodeceptJS delete transaction ${Date.now()}`);
    I.click('Create Transaction');
    I.seeToast('Transaction created successfully');
    I.clickRowAction(displayedAmount, 'Delete');
    I.waitForText('Delete Transaction', 5, 'h2');
    I.click({ xpath: "//button[normalize-space()='Delete']" });
    I.seeToast('Transaction deleted successfully');
    I.dontSee(displayedAmount, 'tbody');
});
