export default class ScoreboardView {
    constructor() {
        this.popup = $("#round-over");

        this.closeButton = this.popup.find('.resume-button');
        this.header = this.popup.find('.popup-header');
        this.table = this.popup.find('.popup-results');
        this.init();
    }

    init() {
        this.initEventListeners();
    }

    initEventListeners() {
        this.closeButton.on('click', () => this.hide());
    }

    update(playerData, gameOver) {
        this.table.empty();

        const leaders = this.getLeaders(playerData);
        if (gameOver) {
            this.header.text(`Game Over! ${leaders[0]} wins!`)
            this.closeButton.hide();
        }

        playerData.forEach((player) => {
            const column = document.createElement("div");
            column.classList.add('popup-results-col');
            if (leaders.includes(player.name))
                column.classList.add('leading');
            const name = document.createElement("div");
            name.classList.add('popup-results-name');
            name.innerHTML = player.name;
            column.append(name);
            const score = document.createElement("div");
            score.classList.add('popup-results-score');
            score.innerHTML = player.score;
            column.append(score);
            this.table.append(column);
        });
    }

    getLeaders(playerData) {
        const leaders = [];
        let minScore = Math.min(...playerData.map(player => player.score));
        playerData.forEach((player) => {
            if (player.score === minScore) {
                leaders.push(player.name);
            }
        });
        return leaders;
    }

    show() {
        this.popup.show();
    }

    hide() {
        this.popup.hide();
    }
}
