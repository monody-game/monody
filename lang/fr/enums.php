<?php

return [
    'badges' => [
        'graphist' => [
            'name' => 'Graphiste',
            'describe' => 'Graphiste de Monody',
            'description' => 'Slt Bawa',
        ],
        'beta' => [
            'name' => 'Bêta-testeur',
            'describe' => 'A participé à la beta de Monody',
            'description' => 'Des chômeurs en sah',
        ],
        'owner' => [
            'name' => 'L\'Originel',
            'describe' => 'Créateur de Monody !',
            'description' => 'moon250',
        ],
        'wins' => [
            'name' => 'Gagnant inarrêtable',
            'describe' => 'A gagné de nombreuses fois',
            'description' => 'Remportez la victoire de nombreuses fois afin de débloquer ce badge.',
        ],
        'losses' => [
            'name' => 'Perdant inépuisable',
            'describe' => 'A perdu de nombreuses fois',
            'description' => 'Perdez de nombreuses fois afin de déloquer ce badge.',
        ],
        'level' => [
            'name' => 'Haut classé',
            'describe' => 'A gravi de nombreux niveaux',
            'description' => 'Acquérez de nombreux niveaux afin de débloquer ce badge.',
        ],
        'rank' => [
            'name' => 'Fou de l\'ELO',
            'describe' => 'S\'est classé en ELO',
            'description' => 'Atteignez les sommets des classements ELO afin de débloquer ce badge !',
        ],
    ],
    'rank' => [
        'villager' => 'Villageois',
        'healer' => 'Guérisseur',
        'oracle' => 'Oracle',
        'elder' => 'Ancien',
        'archbishop' => 'Archevêque',
    ],
    'team' => [
        'villagers' => [
            'name' => 'Villageois',
            'desc' => "Votre but est **d'éliminer les loups et les rôles solitaires** afin de remporter la partie.",
        ],
        'werewolves' => [
            'name' => 'Loups-garous',
            'desc' => "Votre but est **d'éliminer les villageois et les rôles solitaires** afin de remporter la partie.",
        ],
        'loners' => [
            'name' => 'Solitaires',
            'desc' => 'Vous devez gagner seul',
        ],
    ],
    'state' => [
        'waiting' => 'Attente',
        'starting' => 'Démarrage',
        'roles' => 'Distribution des rôles',
        'night' => 'Nuit',
        'cupid' => 'Tour du cupidon',
        'guard' => 'Tour du garde',
        'psychic' => 'Tour de la voyante',
        'werewolf' => 'Tour des loups-garous',
        'infected_werewolf' => 'Tour du loup malade',
        'white_werewolf' => 'Tour du loup blanc',
        'surly_werewolf' => 'Tour du loup hargneux',
        'witch' => 'Tour de la sorcière',
        'parasite' => 'Tour du parasite',
        'day' => 'Jour',
        'mayor' => 'Élection du maire',
        'vote' => 'Vote',
        'end' => 'Fin de la partie',
        'hunter' => 'Tour du chasseur',
        'hunter_message' => 'Le chasseur va tirer sur un joueur pour se venger !',
        'mayor_message' => 'Début de l\'élection du maire. Présentez vous !',
        'vote_message' => 'Début du vote',
    ],
    'roles' => [
        'werewolf' => [
            'name' => 'Loup-garou',
        ],
        'simple_villager' => [
            'name' => 'Simple villageois',
            'describe' => ' Vous ne possédez aucun pouvoir particulier, sauf votre intelligence !',
        ],
        'psychic' => [
            'name' => 'Voyante',
            'describe' => ' Vous pouvez **observer le rôle** d\'un joueur une fois par nuit.',
        ],
        'witch' => [
            'name' => 'Sorcière',
            'describe' => ' Vous disposez de **2 potions**, permettant de respectivement de **tuer** et de **soigner** un joueur. Utilisez les intelligemment !',
        ],
        'little_girl' => [
            'name' => 'Petite fille',
            'describe' => ' Vous pouvez **observer** le chat des loups. Vous ne pouvez pas mourir des loups lorsque le chasseur est en vie.',
        ],
        'elder' => [
            'name' => 'Ancien',
            'describe' => ' Vous disposez d\'une **seconde vie** lorsque vous mourrez la nuit.',
        ],
        'infected_werewolf' => [
            'name' => 'Loup malade',
            'describe' => ' Vous avez la possibiliter **d\'infecter** un joueur tué par les loups, une fois par partie. Le joueur infecté **deviendra un loup**, tout en conservant ses pouvoirs.',
        ],
        'white_werewolf' => [
            'name' => 'Loup blanc',
            'describe' => ', vous gagnez la partie lorsqu\'il ne reste **aucun autre joueur**. Vous disposez d\'un tour supplémentaire, une nuit sur deux, pour **tuer** un joueur. Ce joueur ne peut pas réssusciter quel que soit son rôle.',
        ],
        'angel' => [
            'name' => 'Ange',
            'describe' => '. Au début de la partie une cible vous est assignée. Si cette cible meurt avant la deuxième nuit, vous **remportez la partie instantanément**. Sinon, vous restez en vie sans aucun pouvoir et devez gagner avec le village',
        ],
        'surly_werewolf' => [
            'name' => 'Loup hargneux',
            'describe' => ' Vous vous énervez facilement et vous pouvez **mordre** un joueur une fois par partie. Le joueur mordu **succombera à ses blessures** la nuit suivante.',
        ],
        'parasite' => [
            'name' => 'Parasite',
            'describe' => '. Une fois par nuit, vous pouvez contaminer entre 1 et 2 joueurs. Lorsque tous les joueurs encore en vie sont contaminés, vous **remportez la partie instantanément**.',
        ],
        'cupid' => [
            'name' => 'Cupidon',
            'describe' => ' Vous pouvez également gagner avec le couple. Vous devrez **mettre en couple** deux joueurs. Leur vie sera ainsi liée et si l\'un des amoureux meurt, l\'autre le suivra dans sa tombe.',
        ],
        'guard' => [
            'name' => 'Garde',
            'describe' => ' Vous pouvez **protéger** un joueur par nuit. Le joueur protégé ne peut pas mourir des loups.',
        ],
        'hunter' => [
            'name' => 'Chasseur',
            'describe' => ' À votre mort, vous pourrez **tirer** sur un joueur pour l\'emporter dans la tombe avec vous.',
        ],
    ],
];
