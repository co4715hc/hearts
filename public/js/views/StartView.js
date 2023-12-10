export default class StartView {
    constructor() {
        this.section = $("#game-buttons");
        this.startButton = $('#game-button');
        this.init();
    }

    init() {
        this.startButton.text("Start Game");
        this.initEventListeners();
    }

    initEventListeners() {
        this.startButton.on('click', () => {
            console.log("Starting game");
            document.dispatchEvent(new CustomEvent('startGame', { detail: { playerId: 5}})); // TODO: get player id from somewhere
        });
    }

    show() {
        this.startButton.show();
    }

    hide() {
        this.startButton.hide();
    }
}
