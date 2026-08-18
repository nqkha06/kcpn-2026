const assert = require('node:assert/strict');
const { loadTestData } = require('../../../support/test-data');

Feature('Admin pages - Index shared data');

for (const testCase of loadTestData('admin/pages/index.json')) {
    Scenario(`[${testCase.case_id}] ${testCase.description}`, () => {
        assert.equal(testCase.request.method, 'GET');
        assert.equal(testCase.request.endpoint, '/api/v1/admin/pages');
        assert.ok(Number.isInteger(testCase.expected.status));
    }).tag('@shared-data');
}
