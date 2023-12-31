﻿import GameModel from "./models/GameModel.js";
import PassView from "./views/PassView.js";
import GameController from "./controllers/GameController.js";
import TrickView from "./views/TrickView.js";
import ComputerHandView from "./views/ComputerHandView.js";
import DiscardView from "./views/DiscardView.js";
import ScoreboardView from "./views/ScoreboardView.js";
import LoginController from "./controllers/LoginController.js";
import UserModel from "./models/UserModel.js";
import LoginView from "./views/LoginView.js";
import EventQueueController from "./controllers/EventQueueController.js";

class App {
    constructor() {
        this.userModel = new UserModel();
        this.loginView = new LoginView();
        this.loginController = new LoginController(this.userModel, this.loginView, (playerId) => this.init(playerId));
    }

    init(playerId) {
        this.gameModel = new GameModel(playerId);
        this.passView = new PassView();
        this.trickView = new TrickView();
        this.discardView = new DiscardView();
        this.computerHandView = new ComputerHandView();
        this.scoreboardView = new ScoreboardView();
        this.eventQueueController = new EventQueueController();
        this.gameController = new GameController(this.gameModel, this.passView,
            this.trickView, this.discardView, this.computerHandView, this.scoreboardView, this.eventQueueController);

    }
}

const app = new App();
