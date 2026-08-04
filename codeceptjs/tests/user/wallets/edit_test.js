Feature('User wallets - Edit');

Before(({ I }) => I.loginAsUser());

Scenario('loads and saves an existing wallet', ({ I }) => {
    I.amOnPage('/wallets');
    I.waitForElement('button[aria-label^="Sửa "]', 10);
    I.click('button[aria-label^="Sửa "]');
    I.waitForText('Sửa ví', 5, 'h2');
    I.seeElement('input[placeholder="Ví tiền mặt"]');
    I.seeElement('input[placeholder="Mã tiền tệ"]');
    I.click('Cập nhật ví');
    I.seeToast('Đã cập nhật ví.');
});
