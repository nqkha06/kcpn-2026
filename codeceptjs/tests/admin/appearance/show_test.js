Feature('Admin - Appearance');

Before(({ I }) => {
    I.loginAsAdmin();
});

Scenario('shows logo and localized general settings', ({ I }) => {
    I.openAdminPage('/admin/settings/appearance', 'Appearance');
    I.see('Brand Assets');
    I.seeElement('#logo_light');
    I.seeElement('#logo_dark');
    I.seeElement('#favicon');
    I.seeElement('#social_image');
    I.click({ xpath: "//button[normalize-space()='General']" });
    I.see('General Info');
    I.see('Site Name');
    I.see('Site Title');
    I.see('Tagline');
    I.see('Meta Description');
});
