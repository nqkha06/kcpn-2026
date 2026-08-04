const fs = require('node:fs');
const path = require('node:path');

function loadEnvironmentFile(fileName) {
    const environmentPath = path.join(__dirname, fileName);

    if (!fs.existsSync(environmentPath)) {
        return;
    }

    fs.readFileSync(environmentPath, 'utf8')
        .split(/\r?\n/)
        .forEach((line) => {
            const trimmedLine = line.trim();

            if (!trimmedLine || trimmedLine.startsWith('#')) {
                return;
            }

            const separatorIndex = trimmedLine.indexOf('=');

            if (separatorIndex < 1) {
                return;
            }

            const key = trimmedLine.slice(0, separatorIndex).trim();
            let value = trimmedLine.slice(separatorIndex + 1).trim();

            if (
                (value.startsWith('"') && value.endsWith('"')) ||
                (value.startsWith("'") && value.endsWith("'"))
            ) {
                value = value.slice(1, -1);
            }

            if (process.env[key] === undefined) {
                process.env[key] = value;
            }
        });
}

loadEnvironmentFile('.env');
loadEnvironmentFile('.env.example');

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
            waitForNavigation: 'domcontentloaded',
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
