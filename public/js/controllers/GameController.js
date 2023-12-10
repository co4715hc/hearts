export default class GameController {
    constructor(gameModel, gameView, startView, passView, trickView, discardView, computerHanDView, scoreboardView) {
        this.gameModel = gameModel;
        this.gameView = gameView;
        this.startView = startView;
        this.passView = passView;
        this.trickView = trickView;
        this.discardView = discardView;
        this.computerHandView = computerHanDView;
        this.scoreboardView = scoreboardView;
        this.init();
    }

    init() {
        this.passView.hide();
        this.trickView.hide();
        this.initEventListeners();
    }

    initEventListeners() {
        document.addEventListener('startGame', (event) => this.startGame(event["detail"]["playerId"]));
        document.addEventListener('passCards', (event) => this.passCards(event["detail"]));
        document.addEventListener('playCard', (event) => this.playCard(event["detail"]));
    }

    startGame($playerId) {
        console.log("Starting game", $playerId);
        this.gameModel.startGame()
            .done(response => {
                if (response) {
                    this.handleResponse(response);
                }
            }).fail(error => console.error(error));
    }

    passCards(cards) {
        console.log("Passing cards", cards);
        this.gameModel.passCards(cards['cardIds'])
            .done(response => {
                console.log("Passing cards response", response)
                if (response) {
                    this.handleResponse(response);
                }
            }).fail(error => console.error(error));
    }

    playCard(card) {
        console.log("Playing card", card);
        this.gameModel.playCard(card)
            .done(response => {
                console.log("Playing card response", response)
                if (response) {
                    this.handleResponse(response);
                }
            }).fail(error => console.error(error));
    }

    handleResponse(response) {
        console.log("Handling response", response);
        this.updateState(response.data);
        if (response.state === "passing")
            this.updatePassingState(response.data);
        else if (response.state === "trick")
            this.updateTrickState(response.data);
        else if (response.state === "end")
            this.updateEndState(response.data);
        else
            console.error("Unknown game state", response.state);
    }
    updateState(data) {
        // this.gameView.show();
    }

    updatePassingState(data) {
        this.startView.hide();
        if (data.roundChanged) {
            this.scoreboardView.update(data.playersData);
            this.scoreboardView.show();
        }
        this.passView.update(data.cardHands);
        this.computerHandView.update(data.playersData);
        this.discardView.update(data.playersData);

    }

    updateTrickState(data) {
        this.startView.hide();
        if (data.roundChanged) {
            this.scoreboardView.update(data.playersData);
            this.scoreboardView.show();
        }
        this.passView.hide();
        this.trickView.update(data.cardHands);
        this.computerHandView.update(data.playersData);
        this.discardView.update(data.playersData);

    }

    updateEndState(data) {
        this.passView.hide();
        this.startView.hide();
        this.scoreboardView.update(data.playersData, true);
        this.trickView.update(data.cardHands);
        this.scoreboardView.show();
    }
}
