export default class Card {
    constructor(cardData, cardHandId, callback) {
        this.data = cardData;
        this.cardHandId = cardHandId;
        this.callback = callback;
    }

    render() {
        const cardId = this.data.id;
        const card = document.createElement("div");
        card.id = this.cardHandId;
        card.classList.add("card");
        card.innerHTML = `
        <img src="images/cards/${cardId}.svg" alt="${this.data.value} of ${this.data.suit}">
        `;
        if (this.callback !== undefined) {
            card.addEventListener("click", () => {
                this.callback(card);
            });
            card.classList.add("playable");
        }
        else {
            card.classList.add("unplayable");
        }
        return card;
    }

}
