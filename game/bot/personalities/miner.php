<?php

return [
    'buildings' => [
        'ratios' => [
            'mine' => [GID_B_METAL_MINE => 0.6, GID_B_CRYS_MINE => 0.3, GID_B_DEUT_SYNTH => 0.1],
            'energy' => [GID_B_SOLAR => 0.6, GID_B_FUSION => 0.4],
            'speed' => [GID_B_ROBOTS => 0.5, GID_B_NANITES => 0.5],
            'combat' => [GID_B_SHIPYARD => 0.3, GID_B_MISS_SILO => 0.5, GID_B_ALLY_DEPOT => 0.2],
            'storage' => [GID_B_METAL_STOR => 0.4, GID_B_CRYS_STOR => 0.4, GID_B_DEUT_STOR => 0.2],
            'research' => [GID_B_RES_LAB => 1],
            'special' => [GID_B_TERRAFORMER => 0.5, GID_B_LUNAR_BASE => 0.2, GID_B_PHALANX => 0.1, GID_B_JUMP_GATE => 0.02],
        ],
    ],
    'research' => [
        'ratios' => [
            'combat' => [GID_R_WEAPON => 0.3, GID_R_SHIELD => 0.3, GID_R_ARMOUR => 0.3, GID_R_ESPIONAGE => 0.1],
            'engine' => [GID_R_COMBUST_DRIVE => 0.8, GID_R_IMPULSE_DRIVE => 0.2, GID_R_HYPER_DRIVE => 0.0],
            'advance' => [GID_R_ENERGY => 0.25, GID_R_LASER_TECH => 0.2, GID_R_ION_TECH => 0.2, GID_R_HYPERSPACE => 0.15, GID_R_PLASMA_TECH => 0.2],
            'capacity' => [GID_R_COMPUTER => 0.2, GID_R_IGN => 0.8],
        ],
    ],
    'ships' => [
        'ratios' => [
            'cargo' => [GID_F_SC => 0.2, GID_F_LC => 0.8],
            'support' => [GID_F_COLON => 0.4, GID_F_RECYCLER => 0.4, GID_F_PROBE => 0.2],
            'advanced' => [GID_F_DEATHSTAR => 1.0],
        ],
    ],
    'defense' => [
        'ratios' => [
            'fodder' => [GID_D_RL => 0.4, GID_D_LL => 0.4, GID_D_HL => 0.2],
            'layered' => [GID_D_GAUSS => 0.5, GID_D_ION => 0.5],
            'heavy' => [GID_D_PLASMA => 0.2, GID_D_SDOME => 0.4, GID_D_LDOME => 0.4],
            'missiles' => [GID_D_ABM => 1.0],
        ],
    ],
    'upgrade_priority' => [
        'buildings' => ['mine', 'energy', 'speed', 'storage', 'combat', 'research', 'special'],
        'research' => ['advance', 'engine', 'capacity', 'combat'],
        'defense' => ['missiles', 'fodder', 'layered', 'heavy'],
        'ships' => ['support', 'cargo', 'advanced'],
    ],
];
