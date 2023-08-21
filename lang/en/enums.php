<?php

return [
    'badges' => [
        'graphist' => [
            'name' => 'Graphist',
            'describe' => 'Monody\'s graphic designer',
            'description' => 'Slt Bawa',
        ],
        'beta' => [
            'name' => 'Beta tester',
            'describe' => 'Participated in the Monody beta',
            'description' => 'Des chômeurs en sah',
        ],
        'owner' => [
            'name' => 'The Original',
            'describe' => 'Creator of Monody!',
            'description' => 'moon250',
        ],
        'wins' => [
            'name' => 'Unstoppable Winner',
            'describe' => 'Won many times',
            'description' => 'Win many times to unlock this badge.',
        ],
        'losses' => [
            'name' => 'Inexhaustible loser',
            'describe' => 'Lost many times',
            'description' => 'Lose many times in order to unlock this badge.',
        ],
        'level' => [
            'name' => 'High ranked',
            'describe' => 'Climbed many levels',
            'description' => 'Acquire many levels in order to unlock this badge.',
        ],
    ],
    'team' => [
        'villagers' => [
            'name' => 'Villagers',
            'desc' => 'Your goal is **to eliminate the werewolves and loners** in order to win the game.',
        ],
        'werewolves' => [
            'name' => 'Werewolves',
            'desc' => 'Your goal is **to eliminate the villagers and the loners** in order to win the game.',
        ],
        'loners' => [
            'name' => 'Loners',
            'desc' => 'You have to win on your own',
        ],
    ],
    'state' => [
        'waiting' => 'Waiting',
        'starting' => 'Starting',
        'roles' => 'Distribution of roles',
        'night' => 'Night',
        'cupid' => 'Cupid\'s turn',
        'guard' => 'Guard\'s turn',
        'psychic' => 'Psychic\'s turn',
        'werewolf' => 'Werewolves\' turn',
        'infected_werewolf' => 'Infected wolf\'s turn',
        'white_werewolf' => 'White wolf\'s turn',
        'surly_werewolf' => 'Surly wolf\'s turn',
        'witch' => 'Witch\'s turn',
        'parasite' => 'Parasite\'s turn',
        'day' => 'Day',
        'mayor' => 'Mayor\'s election',
        'vote' => 'Vote',
        'end' => 'End of game',
        'hunter' => 'Hunter\'s turn',
        'hunter_message' => 'Hunter will shoot a player for revenge!',
        'mayor_message' => 'Start of the mayor\'s election. Introduce yourself!',
        'vote_message' => 'Start of voting',
    ],
    'roles' => [
        'werewolf' => [
            'name' => 'Werewolf',
        ],
        'simple_villager' => [
            'name' => 'Simple villager',
            'describe' => ' You don\'t have any special powers, except your intelligence!',
        ],
        'psychic' => [
            'name' => 'Psychic',
            'describe' => ' You can **observe the role** of a player once a night.',
        ],
        'witch' => [
            'name' => 'Witch',
            'describe' => ' You have **2 potions** at your disposal, allowing you to **kill** and **heal** a player respectively. Use them wisely!',
        ],
        'little_girl' => [
            'name' => 'Little girl',
            'describe' => ' You can **observe** the werewolves\' chat. You can\'t die from the wolves while the hunter is alive.',
        ],
        'elder' => [
            'name' => 'Elder',
            'describe' => ' You get a **second life** when you die at night.',
        ],
        'infected_werewolf' => [
            'name' => 'Infected werewolf',
            'describe' => ' You can **infect** a player killed by wolves, once per game. The infected player will **become a wolf**, while retaining his powers.',
        ],
        'white_werewolf' => [
            'name' => 'White werewolf',
            'describe' => 'You win the game when **no other player** is left. You have an extra turn, every other night, to **kill** a player. This player cannot be resurrected, regardless of his or her role.',
        ],
        'angel' => [
            'name' => 'Angel',
            'describe' => '. At the start of the game, you are assigned a target. If this target dies before the second night, you **win the game instantly**. Otherwise, you remain alive with no powers and must win with the village.',
        ],
        'surly_werewolf' => [
            'name' => 'Surly werewolf',
            'describe' => ' You get angry easily and can **bite** a player once per game. The bitten player **will succumb to his wounds** the following night.',
        ],
        'parasite' => [
            'name' => 'Parasite',
            'describe' => '. Once a night, you can contaminate between 1 and 2 players. When all players still alive are contaminated, you **win the game instantly**.',
        ],
        'cupid' => [
            'name' => 'Cupid',
            'describe' => ' You can also win with the couple. You must **pair up** two players. Their lives will be linked, and if one of the lovers dies, the other will follow him to his grave.',
        ],
        'guard' => [
            'name' => 'Guard',
            'describe' => ' You can **protect** one player per night. The protected player cannot die of werewolves.',
        ],
        'hunter' => [
            'name' => 'Hunter',
            'describe' => ' When you die, you can **shoot** on a player to take with you to the grave.',
        ],
    ],
];
