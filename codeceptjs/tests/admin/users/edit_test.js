const assert = require('node:assert/strict');
const { loadTestData } = require('../../../support/test-data');

Feature('Admin users - Update shared data');

for (const testCase of loadTestData('admin/users/update.json')) {
    Scenario(`[${testCase.case_id}] ${testCase.description}`, () => {
        assert.equal(testCase.request.method, 'PUT');
        assert.equal(testCase.request.endpoint, '/api/v1/admin/users/{user}');
        assert.ok(Number.isInteger(testCase.expected.status));
    }).tag('@shared-data');
}
