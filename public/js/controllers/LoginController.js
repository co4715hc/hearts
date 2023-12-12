export default class LoginController {
constructor(userModel, loginView, callback) {
        this.userModel = userModel;
        this.loginView = loginView;
        this.callback = callback;
        this.init();
    }

    init() {
        // console.log("Trying to get ID");
        // this.tryGettingId();
        this.initEventListeners();
    }

    initEventListeners() {
        document.addEventListener('login', (event) => this.login(event["detail"]));
    }

    tryGettingId() {
        this.userModel.getId()
            .done(response => {
                if (response.success) {
                    console.log("Got ID", response.userId);
                    this.loginView.destroy();
                    this.callback(response.userId);
                }
                else {
                    console.log(response);
                    this.loginView.show();
                }
            }).fail(error => console.error(error));
    }

    login(username) {
        console.log("Logging in", username);
        this.userModel.login(username)
            .done(response => {
                if (response) {
                    if (response.success) {
                        console.log("Logged in", response.userId);
                        this.loginView.destroy();
                        this.callback(response.userId);
                    } else
                        console.error("Failed to login for some reason", response);
                }
            }).fail(error => console.error(error));
    }

}
