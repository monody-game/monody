<?php

namespace App\Actions;

use App\Enums\InteractionAction;

interface ActionInterface
{
    /**
     * Indicate if the action can be used more than once
     */
    public function isSingleUse(): bool;

    /**
     * Indicate if the user $userId can use the action $action on the target
     *
     * @param  InteractionAction  $action The action that the user want to use
     * @param  string  $userId The action emitter
     * @param  string  $targetId The action target
     */
    public function canInteract(InteractionAction $action, string $userId, string $targetId = ''): bool;

    /**
     * Call the action
     *
     * @param  string  $targetId The target to call the action on
     * @param  InteractionAction  $action The action to call
     * @param  string  $emitterId The user that used the action
     * @return mixed Result of the action, or void
     */
    public function call(string $targetId, InteractionAction $action, string $emitterId): mixed;

    /**
     * Update clients with data edited after the action (voted players for example)
     */
    public function updateClients(string $userId): void;

    /**
     * Return additional data that should be added to interaction payload
     *
     * @return mixed Could be array, string, null, bool, ...
     */
    public function additionnalData(): mixed;

    /**
     * Actions when closing the interaction
     */
    public function close(): void;

    /**
     * Status of the interaction (e.g. angel's interaction returns a "true" status if the angel has won)
     */
    public function status(): mixed;
}
