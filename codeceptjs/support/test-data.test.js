"use strict";

const assert = require("node:assert/strict");
const test = require("node:test");
const { loadTestData, resolveAliases } = require("./test-data");

const assignedDataFiles = [
    "auth/login.json",
    "auth/register.json",
    "auth/two-factor-challenge.json",
    "auth/forgot-password.json",
    "auth/reset-password.json",
    "public/configuration.json",
    "public/pages-show.json",
    "admin/appearance/update.json",
];

test("loads shared rows and resolves repeat generators", () => {
    const rows = loadTestData("_examples/example.json");

    assert.deepEqual(
        rows.map((row) => row.case_id),
        ["SHARED-DATA-BVA-001", "SHARED-DATA-BVA-002"],
    );
    assert.equal(rows[0].request.body.note.length, 255);
    assert.equal(rows[1].request.body.note.length, 256);
});

test("resolves nested and flat aliases", () => {
    const result = resolveAliases(
        {
            user_id: "@customer.id",
            category_id: "@category.id",
        },
        {
            customer: { id: 42 },
            "category.id": 84,
        },
    );

    assert.deepEqual(result, {
        user_id: 42,
        category_id: 84,
    });
});

test("rejects traversal outside docs/_DataTest", () => {
    assert.throws(
        () => loadTestData("../_Postman/Final.postman_collection.json"),
        /must stay inside docs\/_DataTest/,
    );
});

test("reports missing aliases", () => {
    assert.throws(
        () => resolveAliases("@missing.id", {}),
        /Missing test fixture alias \[@missing\.id\]/,
    );
});

test("assigned operation files follow the complete shared contract with unique case IDs", () => {
    const caseIds = new Set();

    for (const dataFile of assignedDataFiles) {
        const rows = loadTestData(dataFile);

        assert.ok(rows.length > 0, `${dataFile} must contain test cases`);

        for (const row of rows) {
            assert.equal(typeof row.description, "string");
            assert.ok(row.description.length > 0);
            assert.deepEqual(Object.keys(row.request).sort(), [
                "body",
                "endpoint",
                "headers",
                "method",
                "path",
                "query",
            ]);

            for (const key of [
                "status",
                "json_paths",
                "json_absent",
                "validation_errors",
                "database_change",
            ]) {
                assert.ok(
                    Object.hasOwn(row.expected, key),
                    `${row.case_id} missing expected.${key}`,
                );
            }

            assert.equal(
                caseIds.has(row.case_id),
                false,
                `Duplicate global case ID ${row.case_id}`,
            );
            caseIds.add(row.case_id);
        }
    }
});
