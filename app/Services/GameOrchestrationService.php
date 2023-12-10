<?php

namespace App\Services;

use App\Events\GameLifecycle\EndGameEvent;
use App\Events\GameLifecycle\EndPassingEvent;
use App\Events\GameLifecycle\EndRoundEvent;
use App\Events\GameLifecycle\EndTrickPhaseEvent;
use App\Events\GameLifecycle\StartPassingEvent;
use App\Events\GameLifecycle\StartRoundEvent;
use App\Events\GameLifecycle\StartTrickPhaseEvent;
use App\Events\PassingPhase\ComputerPassInputEvent;
use App\Events\PassingPhase\HumanPassInputEvent;
use App\Events\PassingPhase\PassingTurnEvent;
use App\Events\PassingPhase\PlayerPassInputtedEvent;
use App\Events\PassingPhase\PlayerPassTurnEvent;
use App\Events\TrickPhase\ComputerTrickInputEvent;
use App\Events\TrickPhase\EndTrickEvent;
use App\Events\TrickPhase\HumanTrickInputEvent;
use App\Events\TrickPhase\PlayerTrickInputtedEvent;
use App\Events\TrickPhase\PlayerTrickTurnEvent;
use App\Events\TrickPhase\StartTrickEvent;
use App\Events\TrickPhase\TrickTurnEvent;
use App\Listeners\GameLifecycle\StartPassingListener;
use App\Listeners\GameLifecycle\StartTrickPhaseListener;
use App\Listeners\TrickPhase\EndTrickListener;
use App\Models\CardHand;
use App\Models\Game;
use App\Models\GamePlayer;
use App\Models\Hand;
use App\Models\Round;
use App\Models\Trick;
use ErrorException;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class GameOrchestrationService
{
    protected $gameService;
    protected $roundService;
    protected $playerService;
    protected $trickService;

    public function __construct(GameService $gameService, RoundService $roundService, PlayerService $playerService,
                                TrickService $trickService)
    {
        $this->gameService = $gameService;
        $this->roundService = $roundService;
        $this->playerService = $playerService;
        $this->trickService = $trickService;
    }

    // ------------------ Game Phase ------------------

    /**
     * Initiates the game sequence by creating a game and calling for the round to start.
     *
     * Trigger: @see StartGameListener
     * Creates: @see Game
     *          @see GamePlayer
     * Action:  @see StartRoundEvent
     *
     * @param int $playerId
     * @return void
     */
    public function startGameSequence(int $playerId): void
    {
        try{
            Log::Info("startGame: PlayerId: $playerId");
        } catch (Exception $e) {}
        $game = $this->gameService->createGame($playerId);
        event(new StartRoundEvent($game));
    }


    /**
     * End of game. Logs scores.
     *
     * Triggers: @see EndGameListener
     * Actions:
     *
     * @param Game $game
     * @param Collection $scores
     * @return void
     */
    public function startEndGameSequence(Game $game, Collection $scores)
    {
        Log::Info("Game ended");
    }

    // ------------------ Round Phase ------------------

    /**
     * Initiates the round sequence by creating a round and calling for the passing phase to start.
     *
     * Trigger: @see StartRoundListener
     * Creates: @see Round
     *          @see Hand
     *          @see CardHand
     * Action:  @see StartPassingEvent
     *
     * @param Game $game
     * @return void
     */
    public function startRoundSequence(Game $game): void
    {
        try{
            Log::Info("startRound: GameID: $game->id");
        } catch (Exception $e) {}
        $round = $this->roundService->createRound($game);
        event(new StartPassingEvent($round));
    }

    /**
     *  Ends the round sequence and triggers the next round or end of game.
     *
     * Triggers: @see EndRoundListener
     * Actions:  @see StartRoundEvent
     *           @see EndGameEvent
     *
     * @param Round $round
     * @return void
     */
    public function startEndRoundSequence(Round $round)
    {
        $game = $round->game;
        $scores = $this->gameService->calculateGameScores($game);
        $isGameOver = $this->gameService->isGameOver($scores);
        try{
            Log::Info("endRound: RoundID: $round->id" . json_encode($scores));
        } catch (Exception $e) {}
        if ($isGameOver)
            event(new EndGameEvent($game, $scores));
        else
            event(new StartRoundEvent($game));
    }

    // ------------------ Passing Phase ------------------

    /**
     * Initiates the passing sequence by calling for the passing phase to start.
     *
     * Triggers: @see StartPassingListener
     * Actions:  @see EndPassingEvent
     *           @see PassingTurnEvent
     *
     * @param Round $round
     * @return void
     */
    public function startPassingSequence(Round $round): void
    {
        $passingDirection = $this->roundService->getPassingDirection($round);
        try{
            Log::Info("startPassing: RoundID: $round->id, Direction: $passingDirection");
        } catch (Exception $e) {}
        if ($passingDirection === 'none')
            event(new EndPassingEvent($round));
        else
            event(new PassingTurnEvent($round));
    }


    /**
     * Calls for the next player to start their passing turn or for the round to start.
     *
     * Triggers: @see PassingTurnListener
     * Actions:  @see EndPassingEvent
     *           @see PlayerPassTurnEvent
     *
     * @param Round $round
     * @return void
     */
    public function startPassingTurnSequence(Round $round): void
    {
        try{
            Log::Info("passingTurn: RoundID: $round->id");
        } catch (Exception $e) {}
        $nextPlayer = $this->roundService->getNextPlayerToPass($round);
        if ($nextPlayer)
            event(new PlayerPassTurnEvent($round, $nextPlayer));
        else
            event(new EndPassingEvent($round));
    }

    /**
     * Checks if player is a human to determine how to get its input.
     *
     * Triggers: @see PlayerPassTurnListener
     * Actions:  @see HumanPassInputEvent
     *           @see ComputerPassInputEvent
     *
     * @param Round $round
     * @param GamePlayer $player
     * @return void
     */
    public function startPlayerPassTurnSequence(Round $round, GamePlayer $player): void
    {
        try{
            Log::Info("playerPassingTurn: RoundID: $round->id, PlayerId: $player->id");
        } catch (Exception $e) {}
        if ($player->is_human)
            event(new HumanPassInputEvent($round, $player));
        else
            event(new ComputerPassInputEvent($round, $player));
    }

    /**
     * Calls for the computer to input 3 cards to pass.
     *
     * Triggers: @see ComputerPassInputListener
     * Actions:  @see PlayerPassInputtedEvent
     *
     * @param Round $round
     * @param GamePlayer $player
     * @return void
     */
    public function startComputerPassInputSequence(Round $round, GamePlayer $player): void
    {
        try{
            Log::Info("computerPassInput: RoundID: $round->id, PlayerId: $player->id");
        } catch (Exception $e) {}
        $cards = $this->playerService->getCardsToPass($round, $player);
        event(new PlayerPassInputtedEvent($round, $player, $cards));
    }

    /**
     * Attempts to pass the cards and then moves to the next player turn.
     *
     * Triggers: @see PlayerPassInputtedListener
     * Modifies: @see CardHand (hand_id, from_hand_id)
     * Actions:  @see PassingTurnEvent
     *
     * @param Round $round
     * @param GamePlayer $player
     * @param Collection|CardHand[] $cardsToPass
     * @return void
     * @throws ErrorException
     */
    public function startPlayerPassInputtedSequence(Round $round, GamePlayer $player, $cardsToPass): void
    {
        try{
            $cards = [];
            foreach ($cardsToPass as $card)
                $cards[] = $card->card->suit . ' ' . $card->card->rank;
            $cardsString = implode(', ', $cards);
            Log::Info("playerPassInputted: RoundID: $round->id, PlayerId: $player->id, Cards: $cardsString");
        } catch (Exception $e) {}
        $hand = $player->getHandForRound($round);
        $isValid = $this->roundService->isValidPass($hand, $cardsToPass);
        if ($isValid) {
            $handToPassTo = $this->roundService->getHandToPassTo($round, $player);
            $this->roundService->passCards($hand, $handToPassTo, $cardsToPass);
        }
        else
            throw new ErrorException("Computer attempted to pass invalid cards" . json_encode($cardsToPass));
        event(new PassingTurnEvent($round));
    }

    /**
     * Ends passing phase and triggers trick phase.
     *
     * Triggers: @see EndPassingListener
     * Actions:  @see StartTrickPhaseEvent
     *
     * @param Round $round
     * @return void
     */
    public function endPassingSequence(Round $round): void
    {
        try{
            Log::Info("endPassing: RoundID: $round->id");
        } catch (Exception $e) {}
        event(new StartTrickPhaseEvent($round));
    }

    // ------------------ Trick Phase ------------------

    /**
     * Starts a new trick.
     *
     * Triggers: @see StartTrickPhaseListener
     *           @see EndTrickEvent
     * Actions:  @see StartTrickEvent
     *
     * @param Round $round
     * @return void
     */
    public function startTrickPhaseSequence(Round $round): void
    {
        try{
            Log::Info("startTrickPhase: RoundID: $round->id");
        } catch (Exception $e) {}
        event(new StartTrickEvent($round));
    }

    /**
     * Ends the trick and triggers the next trick or round.
     *
     * Triggers: @see EndTrickListener
     * Creates:  @see Trick
     * Actions:  @see StartTrickEvent
     *           @see StartRoundEvent
     *
     * @param Round $round
     * @return void
     */
    public function startTrickSequence(Round $round): void
    {
        try{
            Log::Info("startTrick: RoundID: $round->id");
        } catch (Exception $e) {}
        $trick = $this->trickService->createTrick($round);
        event(new TrickTurnEvent($trick));
    }

    /**
     * Starts the next turn in the trick.
     *
     * Triggers: @see TrickTurnListener
     * Actions:  @see PlayerTrickTurnEvent
     *           @see EndTrickEvent
     *
     * @param Trick $trick
     * @return void
     */
    public function startTrickTurnSequence(Trick $trick): void
    {
        try{
            Log::Info("trickTurn: TrickID: $trick->id");
        } catch (Exception $e) {}
        $nextPlayer = $this->trickService->getNextPlayer($trick);
        if ($nextPlayer)
            event(new PlayerTrickTurnEvent($trick, $nextPlayer));
        else
            event(new EndTrickEvent($trick));
    }

    /**
     * Determine if player is human and start their turn.
     *
     * Triggers: @see PlayerTrickTurnListener
     * Actions:  @see HumanTrickInputEvent
     *           @see ComputerTrickInputEvent
     *
     * @param Trick $trick
     * @param GamePlayer $player
     * @return void
     */
    public function startPlayerTrickTurnSequence(Trick $trick, GamePlayer $player): void
    {
        try{
            Log::Info("playerTrickTurn: TrickID: $trick->id, PlayerId: $player->id");
        } catch (Exception $e) {}
        if ($player->is_human)
            event(new HumanTrickInputEvent($trick, $player));
        else
            event(new ComputerTrickInputEvent($trick, $player));
    }

    /**
     * Gets a random playable card from the computer.
     *
     * Triggers: @see ComputerTrickInputListener
     * Actions:  @see PlayerTrickInputtedEvent
     *
     * @param Trick $trick
     * @param GamePlayer $player
     * @return void
     */
    public function startComputerTrickInputSequence(Trick $trick, GamePlayer $player): void
    {
        try{
            Log::Info("computerTrickInput: TrickId: $trick->id PlayerId: $player->id");
        } catch (Exception $e) {}
        $cardhand = $this->playerService->getCardToPlay($trick, $player);
        event(new PlayerTrickInputtedEvent($trick, $player, $cardhand));
    }

    /**
     * Attempts to play the card and then moves to the next player turn.
     *
     * Triggers: @see PlayerTrickInputtedListener
     * Creates:  @see Discard
     * Actions:  @see TrickTurnEvent
     *
     * @param Trick $trick
     * @param GamePlayer $player
     * @param CardHand $cardhand
     * @return void
     * @throws ErrorException
     */
    public function startPlayerTrickInputtedSequence(Trick $trick, GamePlayer $player, CardHand $cardhand)
    {
        try{
            Log::Info("playerTrickInputted: TrickId: $trick->id PlayerId: $player->id, Card: $cardhand->card");
        } catch (Exception $e) {}
        $isValid = $this->playerService->isValidCard($trick, $player, $cardhand);
        if ($isValid)
            $this->trickService->discardCard($trick, $cardhand);
        else
            throw new ErrorException("Computer attempted to play invalid card" . json_encode($cardhand));
        event(new TrickTurnEvent($trick));
    }

    /**
     * Ends the trick  and triggers the next trick or round.
     *
     * Triggers: @see EndTrickListener
     * Actions:  @see StartTrickPhaseEvent
     *           @see StartRoundEvent
     *
     * @param Trick $trick
     * @return void
     */
    public function startEndTrickSequence(Trick $trick)
    {
        try{
            Log::Info("endTrick: TrickId: $trick->id");
        } catch (Exception $e) {}
        if ($trick->round->tricks()->count() >= 13)
            event(new EndTrickPhaseEvent($trick));
        else
            event(new StartTrickEvent($trick->round));
    }

    /**
     * End trick phase and call for round to end.
     *
     * Triggers: @see EndTrickPhaseListener
     * Actions:  @see EndRoundEvent
     *
     * @param Trick $trick
     * @return void
     */
    public function startEndTrickPhaseSequence(Trick $trick)
    {
        try{
            Log::Info("endTrickPhase: TrickId: $trick->id");
        } catch (Exception $e) {}
        $round = $trick->round;
        event(new EndRoundEvent($round));
    }
}
