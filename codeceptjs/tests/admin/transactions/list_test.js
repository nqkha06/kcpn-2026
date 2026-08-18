const assert = require('node:assert/strict');
const { loadTestData } = require('../../../support/test-data');

Feature('Admin transactions - List shared data');

for (const testCase of loadTestData('admin/transactions/index.json')) {
    Scenario(`[${testCase.case_id}] ${testCase.description}`, () => {
        assert.equal(testCase.request.method, 'GET');
        assert.equal(testCase.request.endpoint, '/api/v1/admin/transactions');
        assert.ok(Number.isInteger(testCase.expected.status));
    }).tag('@shared-data');
}

Feature('Admin transactions - List');

Before(({ I }) => I.loginAsAdmin());

Scenario('lists transactions and exposes detailed filters', ({ I }) => {
    I.openAdminPage('/admin/transactions', 'Transactions');
    I.seeAdminList('Transactions', 'Add Transaction');
    I.see('User', 'th');
    I.see('Wallet', 'th');
    I.see('Category', 'th');
    I.see('Amount', 'th');
    I.see('Transaction Date', 'th');
    I.click({ xpath: "//button[normalize-space()='Filters']" });
    I.see('All Types');
    I.see('All Statuses');
    I.see('From Date');
    I.see('To Date');
});
