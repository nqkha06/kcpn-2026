const assert = require('node:assert/strict');
const { loadTestData } = require('../../../support/test-data');

Feature('Admin categories - Update shared data');

for (const testCase of loadTestData('admin/categories/update.json')) {
    Scenario(`[${testCase.case_id}] ${testCase.description}`, () => {
        assert.equal(testCase.request.method, 'PUT');
        assert.equal(testCase.request.endpoint, '/api/v1/admin/categories/{category}');
        assert.ok(Number.isInteger(testCase.expected.status));
    }).tag('@shared-data');
}
