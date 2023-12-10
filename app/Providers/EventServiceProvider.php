<?php

namespace App\Providers;

use App\Events\GameLifecycle\EndGameEvent;
use App\Events\GameLifecycle\EndPassingEvent;
use App\Events\GameLifecycle\EndRoundEvent;
use App\Events\GameLifecycle\EndTrickPhaseEvent;
use App\Events\GameLifecycle\StartGameEvent;
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
use App\Listeners\GameLifecycle\EndGameListener;
use App\Listeners\GameLifecycle\EndPassingListener;
use App\Listeners\GameLifecycle\EndRoundListener;
use App\Listeners\GameLifecycle\EndTrickPhaseListener;
use App\Listeners\GameLifecycle\StartGameListener;
use App\Listeners\GameLifecycle\StartPassingListener;
use App\Listeners\GameLifecycle\StartRoundListener;
use App\Listeners\GameLifecycle\StartTrickPhaseListener;
use App\Listeners\PassingPhase\ComputerPassInputListener;
use App\Listeners\PassingPhase\HumanPassInputListener;
use App\Listeners\PassingPhase\PassingTurnListener;
use App\Listeners\PassingPhase\PlayerPassInputtedListener;
use App\Listeners\PassingPhase\PlayerPassTurnListener;
use App\Listeners\TrickPhase\ComputerTrickInputListener;
use App\Listeners\TrickPhase\EndTrickListener;
use App\Listeners\TrickPhase\HumanTrickInputListener;
use App\Listeners\TrickPhase\PlayerTrickInputtedListener;
use App\Listeners\TrickPhase\PlayerTrickTurnListener;
use App\Listeners\TrickPhase\StartTrickListener;
use App\Listeners\TrickPhase\TrickTurnListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
     protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        StartGameEvent::class => [
            StartGameListener::class,
        ],
        StartRoundEvent::class => [
            StartRoundListener::class,
        ],
        StartPassingEvent::class => [
            StartPassingListener::class,
        ],
         PassingTurnEvent::class => [
            PassingTurnListener::class,
        ],
        PlayerPassTurnEvent::class => [
            PlayerPassTurnListener::class,
        ],
        ComputerPassInputEvent::class => [
            ComputerPassInputListener::class,
        ],
        PlayerPassInputtedEvent::class => [
            PlayerPassInputtedListener::class,
        ],
        EndPassingEvent::class => [
            EndPassingListener::class,
        ],
        StartTrickPhaseEvent::class => [
            StartTrickPhaseListener::class,
        ],
        StartTrickEvent::class => [
            StartTrickListener::class,
        ],
        EndTrickEvent::class => [
            EndTrickListener::class,
        ],
        EndTrickPhaseEvent::class => [
            EndTrickPhaseListener::class,
        ],
        EndRoundEvent::class => [
            EndRoundListener::class,
        ],
        EndGameEvent::class => [
            EndGameListener::class,
        ],
        TrickTurnEvent::class => [
            TrickTurnListener::class,
        ],
        PlayerTrickTurnEvent::class => [
            PlayerTrickTurnListener::class,
        ],
         HumanTrickInputEvent::class => [
            HumanTrickInputListener::class,
         ],
        ComputerTrickInputEvent::class => [
            ComputerTrickInputListener::class,
        ],
        PlayerTrickInputtedEvent::class => [
            PlayerTrickInputtedListener::class,
        ],
         HumanPassInputEvent::class => [
            HumanPassInputListener::class,
         ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
