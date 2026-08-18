'use strict';

const assert = require('node:assert/strict');
const { loadTestData } = require('../../../support/test-data');

Feature("Admin roles - Delete shared data");

for (const testCase of loadTestData("admin/roles/delete.json")) {
    Scenario(`[${testCase.case_id}] ${testCase.description}`, () => {
        assert.equal(testCase.request.method, "DELETE");
        assert.equal(testCase.request.endpoint, "/api/v1/admin/roles/{role}");
        assert.ok(Number.isInteger(testCase.expected.status));
    }).tag('@shared-data');
}
