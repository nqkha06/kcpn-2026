'use strict';

const assert = require('node:assert/strict');
const { loadTestData } = require('../../../support/test-data');

Feature("Admin menus - Update shared data");

for (const testCase of loadTestData("admin/menus/update.json")) {
    Scenario(`[${testCase.case_id}] ${testCase.description}`, () => {
        assert.equal(testCase.request.method, "PATCH");
        assert.equal(testCase.request.endpoint, "/api/v1/admin/menus/{menu}");
        assert.ok(Number.isInteger(testCase.expected.status));
    }).tag('@shared-data');
}
