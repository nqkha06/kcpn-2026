const assert = require('node:assert/strict');
const { loadTestData } = require('../../../support/test-data');

Feature('User transactions - List shared data');

for (const testCase of loadTestData('user/transactions/index.json')) {
    Scenario(`[${testCase.case_id}] ${testCase.description}`, () => {
        assert.equal(testCase.request.method, 'GET');
        assert.equal(testCase.request.endpoint, '/api/v1/user/transactions');
        assert.ok(Number.isInteger(testCase.expected.status));
    }).tag('@shared-data');
}

Feature('User transactions - List');

Before(({ I }) => I.loginAsUser());

Scenario('shows transactions with search and type filtering', ({ I }) => {
    I.amOnPage('/transactions');
    I.waitForText('Giao dịch', 10, 'h1');
    I.see('Thêm giao dịch', 'button');
    I.seeElement('input[placeholder="Tìm kiếm giao dịch..."]');
    I.see('Tất cả loại', 'select');
    I.see('GIAO DỊCH', 'th');
    I.see('DANH MỤC', 'th');
    I.see('NGÀY', 'th');
    I.see('VÍ', 'th');
    I.see('SỐ TIỀN', 'th');
});
