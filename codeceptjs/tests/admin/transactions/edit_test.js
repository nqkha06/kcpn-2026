const assert = require('node:assert/strict');
const { loadTestData } = require('../../../support/test-data');

Feature('Admin transactions - Edit shared data');

for (const testCase of loadTestData('admin/transactions/update.json')) {
    Scenario(`[${testCase.case_id}] ${testCase.description}`, () => {
        assert.equal(testCase.request.method, 'PUT');
        assert.equal(testCase.request.endpoint, '/api/v1/admin/transactions/{transaction}');
        assert.ok(Number.isInteger(testCase.expected.status));
    }).tag('@shared-data');
}

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
