export default class EventQueueModel {
    constructor() {
        this.lastDiscard = null;
        this.queue = [];
    }

    addEvent(event) {
        this.queue.push(event);
    }

    getEvent() {
        return this.queue.shift();
    }

    isEmpty() {
        return this.queue.length === 0;
    }
}
