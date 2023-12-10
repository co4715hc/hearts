export default class LoginView {
    constructor() {
        this.loginPopup = $('#login-popup');
        this.loginButton = $('#login-button');
        this.loginInput = $('#login-input');
        this.init();
    }

    init() {
        this.loginButton.text("Login");
        this.initEventListeners();
    }

    initEventListeners() {
        this.loginButton.on('click', () => {
            console.log("Logging in");
            let name = this.loginInput.val();
            name = name.replace(/^\s+|\s+$/g, '');
            if (name.length === 0) {
                alert("Please enter a username");
                return;
            }
            document.dispatchEvent(new CustomEvent('login', { detail: name }));
        });
    }

    destroy() {
        this.loginPopup.remove();
    }
}
