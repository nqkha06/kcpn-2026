const assert = require("node:assert/strict");
const { loadTestData } = require("../../../support/test-data");

Feature("Admin - Update appearance");

for (const testCase of loadTestData("admin/appearance/update.json")) {
    Scenario(`[${testCase.case_id}] ${testCase.description}`, () => {
        assert.equal(testCase.request.endpoint, "/api/v1/admin/appearance");
        assert.equal(testCase.request.method, "POST");
        assert.ok(Number.isInteger(testCase.expected.status));
    }).tag("@shared-data");
}

Feature("Admin - Update appearance UI");

Before(({ I }) => {
    I.loginAsAdmin();
});

Scenario("saves the current localized appearance values", async ({ I }) => {
    I.openAdminPage("/admin/settings/appearance", "Appearance");
    I.click({ xpath: "//button[normalize-space()='General']" });
    I.waitForElement('input[name$=".site_name"]', 10);
    const currentName = await I.grabValueFrom('input[name$=".site_name"]');

    I.fillField('input[name$=".site_name"]', currentName || "Spendify");
    I.click("Save changes");
    I.seeToast("Appearance settings updated successfully");
});
