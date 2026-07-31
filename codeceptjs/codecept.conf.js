exports.config = {
    noGlobals: true,
    tests: './tests/**/*_test.js',
    output: './output',
    helpers: {
        Playwright: {
            browser: 'chromium',
            url: process.env.BASE_URL || 'http://localhost:3001',
            show: process.env.HEADLESS !== 'true',
            waitForNavigation: 'networkidle',
            waitForTimeout: 5000,
        },
    },
    include: {
        I: './steps_file.js',
    },
    name: 'cashback-e2e',
};
