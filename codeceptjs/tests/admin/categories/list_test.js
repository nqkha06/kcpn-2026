const assert = require('node:assert/strict');
const { loadTestData } = require('../../../support/test-data');

Feature('Admin categories - Index shared data');

for (const testCase of loadTestData('admin/categories/index.json')) {
    Scenario(`[${testCase.case_id}] ${testCase.description}`, () => {
        assert.equal(testCase.request.method, 'GET');
        assert.equal(testCase.request.endpoint, '/api/v1/admin/categories');
        assert.ok(Number.isInteger(testCase.expected.status));
    }).tag('@shared-data');
}
