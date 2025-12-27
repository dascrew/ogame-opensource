<?php

return [
    'buildings' => [
        'ratios' => [
            'mine' => [GID_B_METAL_MINE => 0.2, GID_B_CRYS_MINE => 0.2, GID_B_DEUT_SYNTH => 0.6],
            'energy' => [GID_B_SOLAR => 1.0, GID_B_FUSION => 0.0],
            'speed' => [GID_B_ROBOTS => 0.6, GID_B_NANITES => 0.4],
            'combat' => [GID_B_SHIPYARD => 0.6, GID_B_ALLY_DEPOT => 0.2, GID_B_MISS_SILO => 0.2],
            'storage' => [GID_B_METAL_STOR => 0.2, GID_B_CRYS_STOR => 0.2, GID_B_DEUT_STOR => 0.6],
            'research' => [GID_B_RES_LAB => 1.0],
            'special' => [GID_B_TERRAFORMER => 0.2, GID_B_LUNAR_BASE => 0.5, GID_B_PHALANX => 0.2, GID_B_JUMP_GATE => 0.1],
        ],
    ],
    'research' => [
        'ratios' => [
            'combat' => [GID_R_WEAPON => 0.2, GID_R_SHIELD => 0.2, GID_R_ARMOUR => 0.2, GID_R_ESPIONAGE => 0.1],
            'engine' => [GID_R_COMBUST_DRIVE => 0.1, GID_R_IMPULSE_DRIVE => 0.5, GID_R_HYPER_DRIVE => 0.3],
            'advance' => [GID_R_ENERGY => 0.5, GID_R_LASER_TECH => 0.1, GID_R_ION_TECH => 0.1, GID_R_HYPERSPACE => 0.2, GID_R_PLASMA_TECH => 0.1],
            'capacity' => [GID_R_COMPUTER => 0.7, GID_R_IGN => 0.3],
            'special' => [GID_R_EXPEDITION => 0.5, GID_R_GRAVITON => 0.5],
        ],
    ],
    'ships' => [
        'ratios' => [
            'cargo' => [GID_F_SC => 0.7, GID_F_LC => 0.3],
            'fodder' => [GID_F_LF => 0.8, GID_F_HF => 0.2],
            'core' => [GID_F_CRUISER => 0.3, GID_F_BATTLESHIP => 0.3, GID_F_DESTRO => 0.1, GID_F_BATTLECRUISER => 0.2, GID_F_BOMBER => 0.1],
            'support' => [GID_F_COLON => 0.1, GID_F_RECYCLER => 0.6, GID_F_PROBE => 0.3],
            'advanced' => [GID_F_DEATHSTAR => 1.0],
        ],
    ],
    'defense' => [
        'ratios' => [
            'fodder' => [GID_D_RL => 0.4, GID_D_LL => 0.3, GID_D_HL => 0.2],
            'layered' => [GID_D_GAUSS => 0.9, GID_D_ION => 0.1],
            'heavy' => [GID_D_PLASMA => 0.0, GID_D_SDOME => 0.5, GID_D_LDOME => 0.5],
            'missiles' => [GID_D_ABM => 0.3, GID_D_IPM => 0.7],
        ],
    ],
    'upgrade_priority' => [
        'buildings' => ['speed', 'combat', 'research', 'energy', 'mine', 'storage', 'special'],
        'research' => ['advance', 'capacity', 'combat', 'engine'],
        'defense' => ['missiles', 'fodder', 'layered', 'heavy'],
        'ships' => ['support', 'fodder', 'core', 'cargo', 'advanced'],
    ],
];
