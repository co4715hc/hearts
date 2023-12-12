export default class LoginView {
    constructor() {
        this.loginPopup = $('#login-popup');
        this.loginButton = $('#login-button');
        this.loginInput = $('#login-input');
        this.init();
    }

    init() {
        this.initEventListeners();
    }

    initEventListeners() {
        this.loginButton.on('click', () => this.loginInteraction());
        this.loginInput.on('keypress', (event) => {
            if (event.key === 'Enter')
                this.loginInteraction();
        })
    }

    loginInteraction() {
            let name = this.loginInput.val();
            name = name.replace(/^\s+|\s+$/g, '');
            if (name.length === 0) {
                alert("Please enter a username");
                return;
            }
            document.dispatchEvent(new CustomEvent('login', { detail: name }));
    }

    destroy() {
        this.loginPopup.remove();
    }
}
