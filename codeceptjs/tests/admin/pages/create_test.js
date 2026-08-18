const assert = require('node:assert/strict');
const { loadTestData } = require('../../../support/test-data');

Feature('Admin pages - Create shared data');

for (const testCase of loadTestData('admin/pages/create.json')) {
    Scenario(`[${testCase.case_id}] ${testCase.description}`, () => {
        assert.equal(testCase.request.method, 'POST');
        assert.equal(testCase.request.endpoint, '/api/v1/admin/pages');
        assert.ok(Number.isInteger(testCase.expected.status));
    }).tag('@shared-data');
}
