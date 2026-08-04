Feature('User wallets - List');

Before(({ I }) => I.loginAsUser());

Scenario('shows wallets and the selected wallet history', ({ I }) => {
    I.amOnPage('/wallets');
    I.waitForText('Ví tiền', 10, 'h1');
    I.see('Thêm ví', 'button');
    I.see('Lịch sử giao dịch', 'h2');
    I.seeElement('article[role="button"]');
});
