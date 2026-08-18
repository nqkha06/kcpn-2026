'use strict';

const assert = require('node:assert/strict');
const { loadTestData } = require('../../../support/test-data');

Feature("Admin permissions - Index shared data");

for (const testCase of loadTestData("admin/permissions/index.json")) {
    Scenario(`[${testCase.case_id}] ${testCase.description}`, () => {
        assert.equal(testCase.request.method, "GET");
        assert.equal(testCase.request.endpoint, "/api/v1/admin/permissions");
        assert.ok(Number.isInteger(testCase.expected.status));
    }).tag('@shared-data');
}
