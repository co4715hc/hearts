import Card from "../components/Card.js";

export default class TrickView {
    constructor() {
        this.playerElement = $('#human');
        this.playerHandElement = this.playerElement.find('.hand');
        this.playerDiscardElement = $('#discard-bottom');
        this.init();
    }

    init() {
        this.initEventListeners();
    }

    initEventListeners() {
    }

    update(data) {
        this.playerHandElement.empty();
        data = Object.values(data);
        data.forEach(cardHand => {
            const card = cardHand.card;
            let cardObject;
            if (cardHand.isPlayable)
                cardObject = new Card(card, cardHand.id, (card) => this.playCard(card));
            else
                cardObject = new Card(card, cardHand.id);
            this.playerHandElement.append(cardObject.render());
        });
    }

    playCard(card) {
        for (const cardElement of this.playerHandElement.children()) {
            if (cardElement.id === card.id) {
                cardElement.remove();
                break;
            }
        }
        document.dispatchEvent(new CustomEvent('discardCard', { detail: card }));
        document.dispatchEvent(new CustomEvent('playCard', { detail: card.id }));

    }

    show() {
        // this.section.show();
    }

    hide() {
        // this.section.hide();
    }
}
