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
            ["Admin API", "Users", "Data / Admin Users / Create"],
            "admin/users/create.json",
        ],
        [
            ["Admin API", "Users", "Data / Admin Users / Index"],
            "admin/users/index.json",
        ],
        [
            ["Admin API", "Users", "Data / Admin Users / Update"],
            "admin/users/update.json",
        ],
        [
            ["Admin API", "Categories", "Data / Admin Categories / Create"],
            "admin/categories/create.json",
        ],
        [
            ["Admin API", "Categories", "Data / Admin Categories / Index"],
            "admin/categories/index.json",
        ],
        [
            ["Admin API", "Categories", "Data / Admin Categories / Update"],
            "admin/categories/update.json",
        ],
        [
            ["Admin API", "Pages", "Data / Admin Pages / Create"],
            "admin/pages/create.json",
        ],
        [
            ["Admin API", "Pages", "Data / Admin Pages / Index"],
            "admin/pages/index.json",
        ],
        [
            ["Admin API", "Pages", "Data / Admin Pages / Update"],
            "admin/pages/update.json",
        ],
        [
            ["Admin API", "Budgets", "Data / Admin Budgets / Create"],
            "admin/budgets/create.json",
        ],
        [
            ["Admin API", "Budgets", "Data / Admin Budgets / Index"],
            "admin/budgets/index.json",
        ],
        [
            ["Admin API", "Budgets", "Data / Admin Budgets / Update"],
            "admin/budgets/update.json",
        ],
        [
            ["Admin API", "Menus", "Data / Admin Menus / Create"],
            "admin/menus/create.json",
        ],
        [
            ["Admin API", "Menus", "Data / Admin Menus / Index"],
            "admin/menus/index.json",
        ],
        [
            ["Admin API", "Menus", "Data / Admin Menus / Update"],
            "admin/menus/update.json",
        ],
        [
            [
                "Admin API",
                "Menus",
                "Data / Admin Menus / Parent Options",
            ],
            "admin/menus/parent-options.json",
        ],
        [
            ["Admin API", "Roles", "Data / Roles / Create"],
            "admin/roles/create.json",
        ],
        [
            ["Admin API", "Roles", "Data / Roles / Index"],
            "admin/roles/index.json",
        ],
        [
            ["Admin API", "Roles", "Data / Roles / Update"],
            "admin/roles/update.json",
        ],
        [
            ["Admin API", "Roles", "Data / Roles / Delete"],
            "admin/roles/delete.json",
        ],
        [
            ["Admin API", "Permissions", "Data / Permissions / Create"],
            "admin/permissions/create.json",
        ],
        [
            ["Admin API", "Permissions", "Data / Permissions / Index"],
            "admin/permissions/index.json",
        ],
        [
            ["Admin API", "Permissions", "Data / Permissions / Update"],
            "admin/permissions/update.json",
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
        [
            ["User API", "Budgets", "Data / User Budgets / Create"],
            "user/budgets/create.json",
        ],
        [
            ["User API", "Settings", "Data / User Settings / Profile"],
            "user/settings/profile.json",
        ],
        [
            ["User API", "Settings", "Data / User Settings / Preferences"],
            "user/settings/preferences.json",
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
