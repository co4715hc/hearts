export default class ScoreboardView {
    constructor() {
        this.popup = $("#round-over");

        this.closeButton = this.popup.find('.popup-close');
        this.header = this.popup.find('.popup-header');
        this.table = this.popup.find('.popup-table');
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

        let headerRow = document.createElement('tr');
        let valuesRow = document.createElement('tr');
        this.table.append(headerRow);
        this.table.append(valuesRow);

        const leaders = this.getLeaders(playerData);
        if (gameOver) {
            this.header.text(`Game Over! ${leaders[0]} wins!`)
        }

        playerData.forEach((player) => {
            let name = document.createElement('th');
            if (leaders.includes(player.name)) {
                name.classList.add('leading');
            }
            name.innerHTML = player.name;
            headerRow.append(name);
            let score = document.createElement('td');
            if (leaders.includes(player.name)) {
                score.classList.add('leading');
            }
            score.innerHTML = player.score;
            valuesRow.append(score);
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
