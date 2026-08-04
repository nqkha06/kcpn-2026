Feature('User transactions - Create');

Before(({ I }) => I.loginAsUser());

Scenario('validates and creates an expense transaction', ({ I }) => {
    const note = `Giao dịch E2E ${Date.now()}`;

    I.amOnPage('/transactions');
    I.waitForText('Giao dịch', 10, 'h1');
    I.click('Thêm giao dịch');
    I.waitForText('Thêm giao dịch', 5, 'h2');
    I.click('Lưu');
    I.see('Số tiền phải lớn hơn 0.');
    I.fillField('input[placeholder="0.00"]', '123456');
    I.fillField('input[placeholder="Khoản này dùng cho gì?"]', note);
    I.fillField('input[placeholder="an-uong, cong-viec"]', 'e2e, codeceptjs');
    I.click('Lưu');
    I.seeToast('Đã thêm giao dịch.');
    I.waitForText(note, 10, 'tbody');
});
