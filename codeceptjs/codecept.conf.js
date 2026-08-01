exports.config = {
    noGlobals: true,
    tests: './tests/**/*_test.js',
    output: './output',
    helpers: {
        Playwright: {
            browser: process.env.BROWSER || 'chromium',
            url: process.env.BASE_URL || 'http://localhost:3001',
            show: process.env.HEADLESS !== 'true',
            windowSize: '1440x900',
            keepBrowserState: false,
            waitForNavigation: 'networkidle',
            waitForTimeout: Number(process.env.WAIT_FOR_TIMEOUT || 5000),
        },
    },
    include: {
        I: './steps_file.js',
    },
    plugins: {
        retryFailedStep: {
            enabled: true,
        },
        screenshot: {
            enabled: true,
            on: 'fail',
            fullPageScreenshots: true,
            uniqueScreenshotNames: true,
        },
    },
    name: 'cashback-e2e',
};
