export default class ComputerHandView {
    constructor() {
        this.computerLeft = $("#computer-1");
        this.computerRight = $("#computer-2");
        this.computerTop = $("#computer-3");
        this.humanName = $("#human").find(".player-name");
        this.computers = [this.computerLeft, this.computerRight, this.computerTop];
    }

    update(playersData) {
        for (let i = 0; i < playersData.length; i++) {
            const playerData = playersData[i];
            if (playerData.isHuman) {
                this.humanName.text(playerData.name);
                continue;
            }
            const nameElement = this.computers[i].find(".player-name");
            const handElement = this.computers[i].find(".hand");
            handElement.empty();

            nameElement.text(playerData.name);
            for (let j = 0; j < playerData.handCount; j++) {
                const cardElement = document.createElement("div");
                cardElement.classList.add("card");
                cardElement.innerHTML = `<img src="images/cards/0.svg" alt="Hidden card">`
                handElement.append(cardElement);
            }
        }
    }
}
