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
    I.fillField('Email address', email);
    I.fillField('Password', password);
    I.click('Log in');
    I.waitInUrl('/dashboard', 10);
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
    });
};
