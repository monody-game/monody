<?php

namespace App\Http\Controllers\Api\Game;

use App\Enums\AlertType;
use App\Enums\GameType;
use App\Enums\State;
use App\Enums\Status;
use App\Events\Bot\ClearGameInvitations;
use App\Events\Bot\ClearVoiceChannels;
use App\Events\Bot\CreateVoiceChannel;
use App\Facades\Redis;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateGameRequest;
use App\Http\Requests\GameIdRequest;
use App\Http\Responses\JsonApiResponse;
use App\Models\User;
use App\Traits\GameHelperTrait;
use App\Traits\MemberHelperTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

use function array_key_exists;

class GameController extends Controller
{
    use GameHelperTrait, MemberHelperTrait;

    public function check(Request $request): JsonApiResponse
    {
        if (!$request->has('gameId')) {
            return new JsonApiResponse(['gameId' => 'Field required.'], Status::UNPROCESSABLE_ENTITY);
        }

        $game = Redis::exists("game:{$request->get('gameId')}");

        if ($game) {
            return new JsonApiResponse(status: Status::NO_CONTENT);
        }

        return JsonApiResponse::make(['message' => "Game {$request->get('gameId')} not found"], Status::NOT_FOUND)
            ->withAlert(AlertType::Error, __('errors.game_not_found'));
    }

    public function new(CreateGameRequest $request): JsonApiResponse
    {
        $data = $request->validated();
        /** @var User $user */
        $user = $request->user();

        if (($request->get('type') & GameType::VOCAL->value) === GameType::VOCAL->value && ($user->discord_id === null || $user->discord_linked_at === null)) {
            return new JsonApiResponse([
                'message' => 'You must link your Discord account to Monody in order to create a vocal game.',
            ], Status::BAD_REQUEST);
        }

        $data['users'] = array_key_exists('users', $data) ? $data['users'] : [];
        $data['roles'] = array_count_values($data['roles']);
        $data['assigned_roles'] = [];
        $data['owner'] = $user->id;
        $data['is_started'] = false;
        $data['dead_users'] = [];
        $id = Str::random(12);
        $data['id'] = $id;
        $data['type'] = $request->get('type') ?: GameType::NORMAL->value;

        if (($data['type'] & GameType::VOCAL->value) === GameType::VOCAL->value) {
            $size = array_reduce($data['roles'], fn ($previous, $role) => $previous + $role, 0);

            broadcast(new CreateVoiceChannel(
                [
                    'game_id' => $id,
                    'owner' => [
                        'username' => $user->username,
                        'discord_id' => $user->discord_id,
                    ],
                    'size' => $size,
                ]
            ));
        }

        if (!array_search($data['owner'], $data['users'], true)) {
            $user->current_game = $id;
            $user->save();
            $data['users'] = array_merge($data['users'], [$data['owner']]);
        }

        $data['owner'] = [
            'id' => $user->id,
            'username' => $user->username,
            'avatar' => $user->avatar,
        ];

        Redis::set("game:$id", $data);
        Redis::set("game:$id:state", [
            'status' => State::Waiting,
            'counterDuration' => State::Waiting->duration(),
            'round' => 0,
            'startTimestamp' => Carbon::now()->timestamp,
        ]);
        Redis::set("game:$id:votes", []);

        return JsonApiResponse::make(['game' => $data])->withoutCache();
    }

    public function delete(GameIdRequest $request): JsonApiResponse
    {
        $gameId = $request->validated('gameId');

        $shared = Redis::get('bot:game:shared') ?? [];
        unset($shared[$gameId]);
        Redis::set('bot:game:shared', $shared);
        broadcast(new ClearGameInvitations);

        $game = Redis::get("game:$gameId");

        if (($game['type'] & GameType::VOCAL->value) === GameType::VOCAL->value) {
            $discordData = Redis::get("game:$gameId:discord");

            if ($discordData) {
                broadcast(new ClearVoiceChannels([
                    'channel_id' => $discordData['voice_channel'],
                    'game_id' => $gameId,
                ]));
            }
        }

        $this->clearRedisKeys($gameId);

        return new JsonApiResponse(status: Status::NO_CONTENT);
    }

    public function discord(string $gameId): JsonApiResponse
    {
        return JsonApiResponse::make([
            'data' => Redis::get("game:$gameId:discord"),
        ]);
    }
}
