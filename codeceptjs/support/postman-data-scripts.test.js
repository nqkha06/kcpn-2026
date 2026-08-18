"use strict";

const assert = require("node:assert/strict");
const fs = require("node:fs");
const path = require("node:path");
const test = require("node:test");

const collectionPath = path.resolve(
    __dirname,
    "../../docs/_Postman/Final.postman_collection.json",
);

function collection() {
    return JSON.parse(fs.readFileSync(collectionPath, "utf8"));
}

function findFolder(document, names) {
    let items = document.item;
    let folder;

    for (const name of names) {
        folder = items.find(
            (item) => item.name === name && Array.isArray(item.item),
        );
        assert.ok(folder, `Missing Postman folder ${names.join(" / ")}`);
        items = folder.item;
    }

    return folder;
}

test("shared Postman scripts compile", () => {
    const events = collection().event;

    assert.deepEqual(
        events.map((event) => event.listen),
        ["prerequest", "test"],
    );

    for (const event of events) {
        assert.doesNotThrow(() => new Function(event.script.exec.join("\n")));
    }
});

test("shared Postman variables are present", () => {
    const variables = Object.fromEntries(
        collection().variable.map((variable) => [variable.key, variable.value]),
    );

    assert.equal(variables.data_driven, "false");
    assert.equal(variables.case_id, "");
    assert.equal(variables.case_endpoint, "");
    assert.equal(variables.case_body, "{}");
    assert.equal(variables.max_response_time_ms, "");
});

test("assigned Postman operations are data-driven by their matching JSON files", () => {
    const operations = [
        [
            ["Authentication", "Admin Session", "POST /auth/login"],
            "auth/login.json",
        ],
        [
            ["Authentication", "Account & Recovery", "POST /auth/register"],
            "auth/register.json",
        ],
        [
            [
                "Authentication",
                "Account & Recovery",
                "POST /auth/two-factor-challenge",
            ],
            "auth/two-factor-challenge.json",
        ],
        [
            [
                "Authentication",
                "Account & Recovery",
                "POST /auth/forgot-password",
            ],
            "auth/forgot-password.json",
        ],
        [
            [
                "Authentication",
                "Account & Recovery",
                "POST /auth/reset-password",
            ],
            "auth/reset-password.json",
        ],
        [
            ["Public API", "GET /public/configuration"],
            "public/configuration.json",
        ],
        [["Public API", "GET /public/pages/{slug}"], "public/pages-show.json"],
        [
            ["Admin API", "Appearance", "POST /admin/appearance"],
            "admin/appearance/update.json",
        ],
        [
            ["Admin API", "Transactions", "Data / Admin Transactions / Create"],
            "admin/transactions/create.json",
        ],
        [
            ["Admin API", "Transactions", "Data / Admin Transactions / Index"],
            "admin/transactions/index.json",
        ],
        [
            ["Admin API", "Transactions", "Data / Admin Transactions / Update"],
            "admin/transactions/update.json",
        ],
        [
            ["User API", "Transactions", "Data / User Transactions / Create"],
            "user/transactions/create.json",
        ],
        [
            ["User API", "Transactions", "Data / User Transactions / Index"],
            "user/transactions/index.json",
        ],
        [
            ["User API", "Wallets", "Data / User Wallets / Create"],
            "user/wallets/create.json",
        ],
        [
            ["User API", "Wallets", "Data / User Wallets / Update"],
            "user/wallets/update.json",
        ],
        [
            ["User API", "Categories", "Data / User Categories / Create"],
            "user/categories/create.json",
        ],
        [
            ["User API", "Categories", "Data / User Categories / Update"],
            "user/categories/update.json",
        ],
        [
            ["User API", "Categories", "Data / User Categories / Delete"],
            "user/categories/delete.json",
        ],
    ];

    for (const [folderPath, dataFile] of operations) {
        const folder = findFolder(collection(), folderPath);
        const variables = Object.fromEntries(
            folder.variable.map((variable) => [variable.key, variable.value]),
        );
        const requestItem = folder.item[0];

        assert.equal(variables.data_driven, "true");
        assert.equal(variables.test_data_file, dataFile);
        assert.equal(
            requestItem.request.url,
            "{{base_url}}{{case_endpoint}}",
        );
        assert.equal(requestItem.request.body.raw, "{{case_body}}");
        assert.equal(
            requestItem.request.header.find(
                (header) => header.key === "X-Data-Driven",
            )?.value,
            "true",
        );
        assert.deepEqual(requestItem.event, []);
    }
});
