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
use App\Enums\Interaction;
use App\Enums\InteractionAction;
use App\Enums\State;
use App\Events\TimeSkip;
use App\Facades\Redis;
use App\Traits\RegisterHelperTrait;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;

class InteractionService
{
    const INTERACTION_DOES_NOT_EXISTS = 1000;

    const NOT_ANY_INTERACTION_STARTED = 2000;

    const USER_CANNOT_USE_THIS_INTERACTION = 3000;

    const INVALID_ACTION_ON_INTERACTION = 4000;

    use RegisterHelperTrait;

    public function __construct(private readonly VoteService $voteService)
    {
    }

    /**
     * Create and start an interaction with the given parameters
     *
     * @param  string  $gameId The id of the game
     * @param  Interaction  $type The type of the interaction (vote, ...)
     * @param  array|string  $authorizedCallers The authorized users to use the interaction (by default it is everyone)
     */
    public function create(string $gameId, Interaction $type, array|string $authorizedCallers = '*'): array
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

        Redis::set($key, [$interaction, ...(Redis::get($key) ?? [])]);

        return $interaction;
    }

    /**
     * @param  string  $id Interaction id
     */
    public function close(string $gameId, string $id): int|null
    {
        if (!Redis::exists("game:$gameId:interactions")) {
            return self::NOT_ANY_INTERACTION_STARTED;
        }

        $interactions = Redis::get("game:$gameId:interactions");
        $interaction = array_search($id, array_column($interactions, 'id'), true);

        if ($interaction === false) {
            return self::INTERACTION_DOES_NOT_EXISTS;
        }

        $service = $this->getService($interactions[$interaction]['type']);

        $service->close($gameId);

        array_splice($interactions, $interaction, 1);

        Redis::set("game:$gameId:interactions", $interactions);

        return null;
    }

    /**
     * @param  string  $id Interaction id
     */
    private function getInteraction(string $gameId, string $id): array
    {
        $interactions = Redis::get("game:$gameId:interactions") ?? [];
        $interaction = array_search($id, array_column($interactions, 'id'), true);

        if ($interaction === false) {
            return [];
        }

        return $interactions[$interaction];
    }

    private function updateInteraction(array $interaction, string $gameId): void
    {
        $interactions = Redis::get("game:$gameId:interactions") ?? [];

        $index = array_search($interaction['id'], array_column($interactions, 'id'), true);

        if ($index === false) {
            return;
        }

        array_splice($interactions, $index, 1);
        $interactions[] = $interaction;

        Redis::set("game:$gameId:interactions", $interactions);
    }

    /**
     * @param  string  $id Interaction id
     */
    public function call(InteractionAction $action, string $id, string $emitterId, string $targetId): mixed
    {
        $gameId = $this->getCurrentUserGameActivity($emitterId);
        $interaction = $this->getInteraction($gameId, $id);
        $type = explode(':', $action->value)[0];

        if ($type !== $interaction['type']) {
            return self::INVALID_ACTION_ON_INTERACTION;
        }

        $service = $this->getService(Interaction::from($type));

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

        $state = Redis::get("game:$gameId:state");
        $state = State::from($state['status']);

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
        $game = Redis::get("game:$gameId");
        $state = Redis::get("game:$gameId:state");

        if ($service->isSingleUse() || (array_key_exists('used', $interaction) && $interaction['used']) && $state['startTimestamp'] - (Date::now()->timestamp - $state['counterDuration']) > 30) {
            return true;
        }

        if (in_array($interaction['type'], [Interaction::Vote->value, Interaction::Mayor->value, Interaction::Werewolves->value], true)) {
            return $this->voteService->hasMajorityVoted($game, $interaction['type']);
        }

        return false;
    }

    public function status(string $gameId, Interaction $type): mixed
    {
        $service = $this->getService($type);

        return $service->status($gameId);
    }

    /**
     * Dictate if the current state has already been skipped in time
     */
    private function timeHasBeenSkipped(string $gameId): bool
    {
        $status = Redis::get("game:$gameId:state");

        return array_key_exists('skipped', $status) && $status['skipped'] === true;
    }

    private function getService(Interaction|string $type): ActionInterface
    {
        if (is_string($type)) {
            $type = Interaction::from($type);
        }

        return match ($type) {
            Interaction::Vote => app(VoteAction::class),
            Interaction::Witch => new WitchAction,
            Interaction::Psychic => new PsychicAction,
            Interaction::Werewolves => app(WerewolvesAction::class),
            Interaction::InfectedWerewolf => new InfectedWerewolfAction,
            Interaction::WhiteWerewolf => app(WhiteWerewolfAction::class),
            Interaction::Mayor => app(MayorAction::class),
            Interaction::Angel => app(AngelAction::class)
        };
    }
}
