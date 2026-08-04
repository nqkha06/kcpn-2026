Feature('User wallets - Delete');

Before(({ I }) => I.loginAsUser());

Scenario('deletes a wallet created for the test', ({ I }) => {
    const name = `Ví xóa E2E ${Date.now()}`;

    I.amOnPage('/wallets');
    I.waitForText('Ví tiền', 10, 'h1');
    I.click('Thêm ví');
    I.fillField('input[placeholder="Ví tiền mặt"]', name);
    I.fillField('input[placeholder="Mã tiền tệ"]', 'VND');
    I.click('Lưu ví');
    I.seeToast('Đã tạo ví mới.');
    I.waitForElement(`button[aria-label="Xóa ${name}"]`, 10);
    I.click(`button[aria-label="Xóa ${name}"]`);
    I.seeToast('Đã xóa ví.');
    I.dontSee(name);
});
