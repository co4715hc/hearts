export default class GameModel {
    constructor(userId) {
        this.userId = userId;
        this.isBusy = false;
    }

    startGame(playerId) {
        console.log("Starting game request");
        if (this.isBusy)
            return $.Deferred().reject("Request is busy").promise();
        this.isBusy = true;
        return $.ajax(
            {
                url: "api/startGame.php",
                type: "POST",
                data: {
                    playerId: this.userId
                },
                dataType: "json"
            }
        ).always(() => this.isBusy = false);
    }

    passCards(cards) {
        console.log("Passing cards request");
        if (this.isBusy)
            return $.Deferred().reject("Request is busy").promise();
        this.isBusy = true;
        return $.ajax(
            {
                url: "api/passCards.php",
                type: "POST",
                data: {
                    cards: cards
                },
                dataType: "json"
            }
        ).always(() => this.isBusy = false);
    }

    playCard(card) {
        console.log("Playing card request");
        if (this.isBusy)
            return $.Deferred().reject("Request is busy").promise();
        this.isBusy = true;
        return $.ajax(
            {
                url: "api/playCard.php",
                type: "POST",
                data: {
                    card: card
                },
                dataType: "json"
            }
        ).always(() => this.isBusy = false);
    }
}
