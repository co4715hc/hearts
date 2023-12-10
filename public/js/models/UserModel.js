export default class UserModel {

    getId() {
        return $.ajax(
            {
                url: "api/getId.php",
                type: "GET",
                dataType: "json"
            }
        );
    }

    login(username) {
        return $.ajax(
            {
                url: "api/login.php",
                type: "POST",
                data: {
                    username: username
                },
                dataType: "json"
            }
        );
    }
}
