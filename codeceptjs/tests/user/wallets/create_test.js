const assert = require('node:assert/strict');
const { loadTestData } = require('../../../support/test-data');

Feature('User wallets - Create shared data');

for (const testCase of loadTestData('user/wallets/create.json')) {
    Scenario(`[${testCase.case_id}] ${testCase.description}`, () => {
        assert.equal(testCase.request.method, 'POST');
        assert.equal(testCase.request.endpoint, '/api/v1/user/wallets');
        assert.ok(Number.isInteger(testCase.expected.status));
    }).tag('@shared-data');
}

Feature('User wallets - Create');

Before(({ I }) => I.loginAsUser());

Scenario('validates and creates a wallet', ({ I }) => {
    const name = `Ví E2E ${Date.now()}`;
    const balanceField = { xpath: "//label[.//span[normalize-space()='Số dư ban đầu']]//input" };

    I.amOnPage('/wallets');
    I.waitForText('Ví tiền', 10, 'h1');
    I.click('Thêm ví');
    I.waitForText('Thêm ví', 5, 'h2');
    I.clearField('input[placeholder="Ví tiền mặt"]');
    I.click('Lưu ví');
    I.see('Vui lòng nhập tên ví.');
    I.fillField('input[placeholder="Ví tiền mặt"]', name);
    I.fillField('input[placeholder="Mã tiền tệ"]', 'VND');
    I.fillField(balanceField, '100000');
    I.click('Lưu ví');
    I.seeToast('Đã tạo ví mới.');
    I.waitForText(name, 10);
});
