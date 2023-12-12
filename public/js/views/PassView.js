import Card from "../components/Card.js";


export default class PassView {
    constructor() {
        this.playerElement = $('#human');
        this.playerHandElement = this.playerElement.find('.hand');
        this.gameButton = $('#pass-button');
        this.init();
    }

    init() {
        this.initEventListeners();
    }

    initEventListeners() {
        this.gameButton.on('click', () => {
            const cardIds = this.getSelectedCardIds();
            if (cardIds.length !== 3) {
                alert('You must select 3 cards to pass.');
                return false;
            }
            this.hide();
            document.dispatchEvent(new CustomEvent('passCards', { detail: { cardIds } }));
        });
    }

    update(data) {
        this.playerHandElement.empty();
        data.forEach(cardHand => {
            const card = cardHand.card;
            const cardObject = new Card(card, cardHand.id, (card) => this.toggleCard(card));
            this.playerHandElement.append(cardObject.render());
        });
        this.show();
    }

    toggleCard(card) {
        if (card.classList.contains('selected')) {
            card.classList.remove('selected');
        } else if (this.getSelectedCardIds().length < 3) {
            card.classList.add('selected');
        }
    }

    show() {
        this.gameButton.show();
    }

    hide() {
        this.gameButton.hide();
    }

    getSelectedCardIds() {
        return this.playerHandElement.find('.card.selected').map(function() {
            return this.id;
        }).get();
    }
}
