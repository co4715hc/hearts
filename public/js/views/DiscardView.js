export default class DiscardView {
    constructor() {
        this.discardLeft = $("#discard-left");
        this.discardTop = $("#discard-top");
        this.discardRight = $("#discard-right");
        this.discardBottom = $("#discard-bottom");
        this.discards = [this.discardLeft, this.discardTop, this.discardRight, this.discardBottom];
    }

    update(playersData) {
        for (let i = 0; i < playersData.length; i++) {
            const playerData = playersData[i];
            const discardElement = this.discards[i];
            // discardElement.empty();
            const discard = playerData.discarded;
            if (discard) {
                const cardElement = document.createElement("div");
                cardElement.classList.add("card");
                // cardElement.innerHTML = `<img src="images/cards/${discard.id}.svg" alt="${discard.rank} of ${discard.suit}">`
                discardElement.append(cardElement);
            }


            for (let j = 0; j < playerData.discardCount; j++) {
                const cardElement = document.createElement("div");
                cardElement.classList.add("card");
                cardElement.innerHTML = `<img src="images/cards/${playerData.discard[j]}.svg" alt="Hidden card">`
                discardElement.append(cardElement);
            }
        }
    }
}
