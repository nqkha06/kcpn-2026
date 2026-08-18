const assert = require('node:assert/strict');
const { loadTestData } = require('../../../support/test-data');

Feature('Admin categories - Create shared data');

for (const testCase of loadTestData('admin/categories/create.json')) {
    Scenario(`[${testCase.case_id}] ${testCase.description}`, () => {
        assert.equal(testCase.request.method, 'POST');
        assert.equal(testCase.request.endpoint, '/api/v1/admin/categories');
        assert.ok(Number.isInteger(testCase.expected.status));
    }).tag('@shared-data');
}
