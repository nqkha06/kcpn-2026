const assert = require('node:assert/strict');
const { loadTestData } = require('../../../support/test-data');

Feature('User wallets - Edit shared data');

for (const testCase of loadTestData('user/wallets/update.json')) {
    Scenario(`[${testCase.case_id}] ${testCase.description}`, () => {
        assert.equal(testCase.request.method, 'PUT');
        assert.equal(testCase.request.endpoint, '/api/v1/user/wallets/{wallet}');
        assert.ok(Number.isInteger(testCase.expected.status));
    }).tag('@shared-data');
}

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
