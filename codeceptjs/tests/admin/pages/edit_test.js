const assert = require('node:assert/strict');
const { loadTestData } = require('../../../support/test-data');

Feature('Admin pages - Update shared data');

for (const testCase of loadTestData('admin/pages/update.json')) {
    Scenario(`[${testCase.case_id}] ${testCase.description}`, () => {
        assert.equal(testCase.request.method, 'PUT');
        assert.equal(testCase.request.endpoint, '/api/v1/admin/pages/{page}');
        assert.ok(Number.isInteger(testCase.expected.status));
    }).tag('@shared-data');
}
