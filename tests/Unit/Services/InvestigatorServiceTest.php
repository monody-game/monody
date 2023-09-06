<?php

namespace Services;

use App\Enums\Role;
use App\Facades\Redis;
use App\Models\User;
use App\Services\Vote\InvestigatorService;
use Tests\TestCase;

class InvestigatorServiceTest extends TestCase
{
    private array $game;

    public function testComparingTwoUsersFromSameTeam()
    {
        $service = new InvestigatorService();

        $service->vote('investigator', $this->game['id'], 'sv');
        $service->vote('investigator', $this->game['id'], 'investigator');

        $result = $service->compare($this->game['id']);
        $this->assertSame($service::SAME_TEAM, $result);
    }

    public function testComparingTwoUsersFromDifferentTeams()
    {
        $service = new InvestigatorService();

        $service->vote('investigator', $this->game['id'], 'sv');
        $service->vote('investigator', $this->game['id'], 'werewolf');

        $result = $service->compare($this->game['id']);
        $this->assertSame($service::DIFFERENT_TEAM, $result);
    }

    public function testComparingALoner()
    {
        $service = new InvestigatorService();

        $service->vote('investigator', $this->game['id'], 'sv');
        $service->vote('investigator', $this->game['id'], 'parasite');

        $result = $service->compare($this->game['id']);
        $this->assertSame($service::DIFFERENT_TEAM, $result);
    }

    public function testComparingTwoLoners()
    {
        $service = new InvestigatorService();

        $service->vote('investigator', $this->game['id'], 'whitewerewolf');
        $service->vote('investigator', $this->game['id'], 'parasite');

        $result = $service->compare($this->game['id']);
        $this->assertSame($service::DIFFERENT_TEAM, $result);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();

        $this->game = $this
            ->actingAs($user)
            ->put('/api/game', [
                'roles' => [Role::Investigator->value, Role::SimpleVillager->value, Role::Werewolf->value, Role::Parasite->value, Role::WhiteWerewolf->value],
                'users' => ['investigator', 'sv', 'werewolf', 'parasite', 'whitewerewolf'],
            ])
            ->json('data.game');

        Redis::update("game:{$this->game['id']}", fn (array &$game) => $game['assigned_roles'] = [
            'investigator' => Role::Investigator->value, 'sv' => Role::SimpleVillager->value,
            'werewolf' => Role::Werewolf->value, 'parasite' => Role::Parasite->value,
            'whitewerewolf' => Role::WhiteWerewolf->value,
        ]);
    }
}
