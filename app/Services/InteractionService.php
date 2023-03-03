<?php

namespace App\Services;

use App\Actions\ActionInterface;
use App\Actions\AngelAction;
use App\Actions\InfectedWerewolfAction;
use App\Actions\MayorAction;
use App\Actions\PsychicAction;
use App\Actions\VoteAction;
use App\Actions\WerewolvesAction;
use App\Actions\WhiteWerewolfAction;
use App\Actions\WitchAction;
use App\Enums\InteractionActions;
use App\Enums\Interactions;
use App\Enums\States;
use App\Events\TimeSkip;
use App\Traits\InteractsWithRedis;
use App\Traits\RegisterHelperTrait;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;

class InteractionService
{
    const INTERACTION_DOES_NOT_EXISTS = 1000;

    const NOT_ANY_INTERACTION_STARTED = 2000;

    const USER_CANNOT_USE_THIS_INTERACTION = 3000;

    const INVALID_ACTION_ON_INTERACTION = 4000;

    use RegisterHelperTrait, InteractsWithRedis;

    public function __construct(private readonly VoteService $voteService)
    {
    }

    /**
     * Create and start an interaction with the given parameters
     *
     * @param  string  $gameId The id of the game
     * @param  Interactions  $type The type of the interaction (vote, ...)
     * @param  array|string  $authorizedCallers The authorized users to use the interaction (by default it is everyone)
     */
    public function create(string $gameId, Interactions $type, array|string $authorizedCallers = '*'): array
    {
        $key = "game:$gameId:interactions";
        /** @var string $callers */
        $callers = is_string($authorizedCallers) ? $authorizedCallers : json_encode($authorizedCallers);

        $interaction = [
            'gameId' => $gameId,
            'id' => (string) Str::uuid(),
            'authorizedCallers' => $callers,
            'type' => $type->value,
        ];

        $action = $this->getService($type);
        $data = $action->additionnalData($gameId);

        if ($data !== null) {
            $interaction['data'] = $data;
        }

        $this->redis()->set($key, [$interaction, ...($this->redis()->get($key) ?? [])]);

        return $interaction;
    }

    /**
     * @param  string  $id Interaction id
     */
    public function close(string $gameId, string $id): int|null
    {
        if (!$this->redis()->exists("game:$gameId:interactions")) {
            return self::NOT_ANY_INTERACTION_STARTED;
        }

        $interactions = $this->redis()->get("game:$gameId:interactions");
        $interaction = array_search($id, array_column($interactions, 'id'), true);

        if ($interaction === false) {
            return self::INTERACTION_DOES_NOT_EXISTS;
        }

        $service = $this->getService($interactions[$interaction]['type']);

        $service->close($gameId);

        array_splice($interactions, $interaction, 1);

        $this->redis()->set("game:$gameId:interactions", $interactions);

        return null;
    }

    /**
     * @param  string  $id Interaction id
     */
    private function getInteraction(string $gameId, string $id): array
    {
        $interactions = $this->redis()->get("game:$gameId:interactions") ?? [];
        $interaction = array_search($id, array_column($interactions, 'id'), true);

        if ($interaction === false) {
            return [];
        }

        return $interactions[$interaction];
    }

    private function updateInteraction(array $interaction, string $gameId): void
    {
        $interactions = $this->redis()->get("game:$gameId:interactions") ?? [];

        $index = array_search($interaction['id'], array_column($interactions, 'id'), true);

        if ($index === false) {
            return;
        }

        array_splice($interactions, $index, 1);
        $interactions[] = $interaction;

        $this->redis()->set("game:$gameId:interactions", $interactions);
    }

    /**
     * @param  string  $id Interaction id
     */
    public function call(InteractionActions $action, string $id, string $emitterId, string $targetId): mixed
    {
        $gameId = $this->getCurrentUserGameActivity($emitterId);
        $interaction = $this->getInteraction($gameId, $id);
        $type = explode(':', $action->value)[0];

        if ($type !== $interaction['type']) {
            return self::INVALID_ACTION_ON_INTERACTION;
        }

        $service = $this->getService(Interactions::from($type));

        if (
            !$service->canInteract($action, $emitterId, $targetId) ||
            ($service->isSingleUse() && array_key_exists('used', $interaction) && $interaction['used'])
        ) {
            return self::USER_CANNOT_USE_THIS_INTERACTION;
        }

        $status = $service->call($targetId, $action, $emitterId);

        if ($service->isSingleUse()) {
            $interaction['used'] = true;
            $this->updateInteraction($interaction, $gameId);
        }

        $service->updateClients($emitterId);

        if (!$this->shouldSkipTime($id, $gameId) || $this->timeHasBeenSkipped($gameId)) {
            return $status;
        }

        $state = $this->redis()->get("game:$gameId:state");
        $state = States::from($state['status']);

        $skipDuration = $state->getTimeSkip();

        /**
         * Should not happen in production. Used to patch test cases
         */
        if ($skipDuration === null) {
            return $status;
        }

        broadcast(new TimeSkip($gameId, $skipDuration));

        return $status;
    }

    /**
     * Dictate if state's duration should be skipped, depending on interaction status
     *
     * @param  string  $id Interaction id
     */
    public function shouldSkipTime(string $id, string $gameId): bool
    {
        $interaction = $this->getInteraction($gameId, $id);
        $service = $this->getService($interaction['type']);
        $game = $this->redis()->get("game:$gameId");
        $state = $this->redis()->get("game:$gameId:state");

        if ($service->isSingleUse() || (array_key_exists('used', $interaction) && $interaction['used']) && $state['startTimestamp'] - (Date::now()->timestamp - $state['counterDuration']) > 30) {
            return true;
        }

        if (in_array($interaction['type'], [Interactions::Vote->value, Interactions::Mayor->value, Interactions::Werewolves->value], true)) {
            return $this->voteService->hasMajorityVoted($game, $interaction['type']);
        }

        return false;
    }

    public function status(string $gameId, Interactions $type): mixed
    {
        $service = $this->getService($type);

        return $service->status($gameId);
    }

    /**
     * Dictate if the current state has already been skipped in time
     */
    private function timeHasBeenSkipped(string $gameId): bool
    {
        $status = $this->redis()->get("game:$gameId:state");

        return array_key_exists('skipped', $status) && $status['skipped'] === true;
    }

    private function getService(Interactions|string $type): ActionInterface
    {
        if (is_string($type)) {
            $type = Interactions::from($type);
        }

        return match ($type) {
            Interactions::Vote => app(VoteAction::class),
            Interactions::Witch => new WitchAction,
            Interactions::Psychic => new PsychicAction,
            Interactions::Werewolves => app(WerewolvesAction::class),
            Interactions::InfectedWerewolf => new InfectedWerewolfAction,
            Interactions::WhiteWerewolf => app(WhiteWerewolfAction::class),
            Interactions::Mayor => app(MayorAction::class),
            Interactions::Angel => app(AngelAction::class)
        };
    }
}
