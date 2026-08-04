const { actor } = require('codeceptjs');

function requiredEnvironmentValue(name) {
    const value = process.env[name];

    if (!value) {
        throw new Error(`Missing required CodeceptJS environment variable: ${name}`);
    }

    return value;
}

function login(I, email, password) {
    I.amOnPage('/login');
    I.waitForElement('#email', 20);
    I.fillField('Email address', email);
    I.fillField('Password', password);
    I.click('Log in');
    I.waitInUrl('/dashboard', 20);
}

function rowActionLocator(text, action) {
    return {
        xpath: `//tbody/tr[.//*[contains(normalize-space(), ${JSON.stringify(text)})]]//button[@aria-label=${JSON.stringify(action)}]`,
    };
}

module.exports = function () {
    return actor({
        loginAsAdmin() {
            login(
                this,
                requiredEnvironmentValue('ADMIN_EMAIL'),
                requiredEnvironmentValue('ADMIN_PASSWORD'),
            );
        },

        loginAsUser() {
            login(
                this,
                requiredEnvironmentValue('USER_EMAIL'),
                requiredEnvironmentValue('USER_PASSWORD'),
            );
        },

        logout(buttonText = 'Đăng xuất') {
            this.click(buttonText);
            this.waitInUrl('/login', 10);
        },

        openAdminPage(path, heading) {
            this.amOnPage(path);
            this.waitForText(heading, 10, 'h2');
        },

        seeAdminList(heading, createLabel) {
            this.waitForText(heading, 10, 'h2');
            this.see(createLabel, 'a');
            this.seeElement('input[placeholder="Search..."]');
            this.seeElement('table');
        },

        selectFirstOption(selector) {
            this.executeScript((selectSelector) => {
                const select = document.querySelector(selectSelector);
                const option = Array.from(select?.options ?? []).find((item) => item.value !== '');

                if (!select || !option) {
                    throw new Error(`No selectable option found for ${selectSelector}`);
                }

                select.value = option.value;
                select.dispatchEvent(new Event('change', { bubbles: true }));
            }, selector);
        },

        selectOptionContaining(selector, text) {
            this.executeScript(({ selectSelector, optionText }) => {
                const select = document.querySelector(selectSelector);
                const option = Array.from(select?.options ?? []).find(
                    (item) => item.value !== '' && item.textContent?.includes(optionText),
                );

                if (!select || !option) {
                    throw new Error(
                        `No option containing "${optionText}" found for ${selectSelector}`,
                    );
                }

                select.value = option.value;
                select.dispatchEvent(new Event('input', { bubbles: true }));
                select.dispatchEvent(new Event('change', { bubbles: true }));
            }, { selectSelector: selector, optionText: text });
        },

        waitForPathEnding(path, timeout = 10) {
            this.waitForFunction(
                (expectedPath) => window.location.pathname.endsWith(expectedPath),
                [path],
                timeout,
            );
        },

        waitForFieldValue(selector, timeout = 10) {
            this.waitForFunction(
                (fieldSelector) => Boolean(document.querySelector(fieldSelector)?.value),
                [selector],
                timeout,
            );
        },

        waitForSelectableOption(selector, timeout = 10) {
            this.waitForFunction(
                (selectSelector) => Array.from(
                    document.querySelector(selectSelector)?.options ?? [],
                ).some((option) => option.value !== ''),
                [selector],
                timeout,
            );
        },

        clickFirstTableAction(action) {
            this.waitForElement(`tbody tr button[aria-label="${action}"]`, 10);
            this.click(`tbody tr button[aria-label="${action}"]`);
        },

        clickRowAction(text, action) {
            this.waitForElement(rowActionLocator(text, action), 10);
            this.click(rowActionLocator(text, action));
        },

        seeToast(message) {
            this.waitForText(message, 10);
        },
    });
};
