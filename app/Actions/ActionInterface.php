<?php

namespace App\Actions;

use App\Enums\InteractionActions;

interface ActionInterface
{

	/**
	 * Indicate if the action can be used more than once
	 *
	 * @return bool
	 */
	public function isSingleUse(): bool;

	/**
	 * Indicate if the user $userId can use the action $action on the target
	 *
	 * @param InteractionActions $action The action that the user want to use
	 * @param string $userId The action emitter
	 * @param string $targetId The action target
	 *
	 * @return bool
	 */
    public function canInteract(InteractionActions $action, string $userId, string $targetId = ''): bool;

	/**
	 * Call the action
	 *
	 * @param string $targetId The target to call the action on
	 * @param InteractionActions $action The action to call
	 *
	 * @return mixed Result of the action, or void
	 */
    public function call(string $targetId, InteractionActions $action): mixed;

	/**
	 * Update clients with data edited after the action (voted players for example)
	 *
	 * @param string $userId
	 * @return void
	 */
    public function updateClients(string $userId): void;

	/**
	 * Actions when closing the interaction
	 *
	 * @param string $gameId
	 * @return void
	 */
    public function close(string $gameId): void;
}
