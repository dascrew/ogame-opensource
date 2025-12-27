<?php

// Requirements data structure for buildings
$buildingRequirements = [
    GID_B_METAL_MINE => [],
    GID_B_CRYS_MINE => [],
    GID_B_DEUT_SYNTH => [],
    GID_B_SOLAR => [],
    GID_B_FUSION => [
        'buildings' => ['b3' => 5],
        'research' => ['r113' => 3]
    ],
    GID_B_ROBOTS => [],
    GID_B_NANITES => [
        'buildings' => ['b14' => 10],
        'research' => ['r108' => 10]
    ],
    GID_B_SHIPYARD => [
        'buildings' => ['b14' => 2]
    ],
    GID_B_METAL_STOR => [],
    GID_B_CRYS_STOR => [],
    GID_B_DEUT_STOR => [],
    GID_B_RES_LAB => [],
    GID_B_TERRAFORMER => [
        'buildings' => ['b15' => 1],
        'research' => ['r113' => 12]
    ],
    GID_B_ALLY_DEPOT => [],
    GID_B_MISS_SILO => [
        'buildings' => ['b21' => 1]
    ],
    GID_B_LUNAR_BASE => [],
    GID_B_PHALANX => [
        'buildings' => ['b41' => 1]
    ],
    GID_B_JUMP_GATE => [
        'buildings' => ['b41' => 1],
        'research' => ['r114' => 7]
    ],
];

// Requirements for ships and defense
$shipyardRequirements = [
    GID_F_SC => [
        'buildings' => ['b21' => 2],
        'research' => ['r115' => 2]
    ],
    GID_F_LC => [
        'buildings' => ['b21' => 4],
        'research' => ['r115' => 6]
    ],
    GID_F_LF => [
        'buildings' => ['b21' => 1],
        'research' => ['r115' => 1]
    ],
    GID_F_HF => [
        'buildings' => ['b21' => 3],
        'research' => ['r111' => 2, 'r117' => 2]
    ],
    GID_F_CRUISER => [
        'buildings' => ['b21' => 5],
        'research' => ['r117' => 4, 'r121' => 2]
    ],
    GID_F_BATTLESHIP => [
        'buildings' => ['b21' => 7],
        'research' => ['r118' => 4]
    ],
    GID_F_COLON => [
        'buildings' => ['b21' => 4],
        'research' => ['r117' => 3]
    ],
    GID_F_RECYCLER => [
        'buildings' => ['b21' => 4],
        'research' => ['r115' => 6, 'r110' => 2]
    ],
    GID_F_PROBE => [
        'buildings' => ['b21' => 3],
        'research' => ['r115' => 3, 'r106' => 2]
    ],
    GID_F_BOMBER => [
        'buildings' => ['b21' => 8],
        'research' => ['r117' => 6, 'r122' => 5]
    ],
    GID_F_SAT => [
        'buildings' => ['b21' => 1]
    ],
    GID_F_DESTRO => [
        'buildings' => ['b21' => 9],
        'research' => ['r118' => 6, 'r114' => 5]
    ],
    GID_F_DEATHSTAR => [
        'buildings' => ['b21' => 12],
        'research' => ['r118' => 7, 'r114' => 6, 'r199' => 1]
    ],
    GID_F_BATTLECRUISER => [
        'buildings' => ['b21' => 8],
        'research' => ['r114' => 5, 'r120' => 12, 'r118' => 5]
    ],

    // Defense
    GID_D_RL => [
        'buildings' => ['b21' => 1]
    ],
    GID_D_LL => [
        'buildings' => ['b21' => 2],
        'research' => ['r113' => 1, 'r120' => 3]
    ],
    GID_D_HL => [
        'buildings' => ['b21' => 4],
        'research' => ['r113' => 3, 'r120' => 6]
    ],
    GID_D_GAUSS => [
        'buildings' => ['b21' => 6],
        'research' => ['r113' => 6, 'r109' => 3, 'r110' => 1]
    ],
    GID_D_ION => [
        'buildings' => ['b21' => 4],
        'research' => ['r121' => 4]
    ],
    GID_D_PLASMA => [
        'buildings' => ['b21' => 8],
        'research' => ['r122' => 7]
    ],
    GID_D_SDOME => [
        'buildings' => ['b21' => 1],
        'research' => ['r110' => 2]
    ],
    GID_D_LDOME => [
        'buildings' => ['b21' => 6],
        'research' => ['r110' => 6]
    ],
    GID_D_ABM => [
        'buildings' => ['b21' => 1, 'b44' => 2]
    ],
    GID_D_IPM => [
        'buildings' => ['b21' => 1, 'b44' => 4],
        'research' => ['r117' => 1]
    ],
];

// Requirements for research
$researchRequirements = [
    GID_R_ESPIONAGE => [
        'buildings' => ['b31' => 3]
    ],
    GID_R_COMPUTER => [
        'buildings' => ['b31' => 1]
    ],
    GID_R_WEAPON => [
        'buildings' => ['b31' => 4]
    ],
    GID_R_SHIELD => [
        'buildings' => ['b31' => 6],
        'research' => ['r113' => 3]
    ],
    GID_R_ARMOUR => [
        'buildings' => ['b31' => 2]
    ],
    GID_R_ENERGY => [
        'buildings' => ['b31' => 1]
    ],
    GID_R_HYPERSPACE => [
        'buildings' => ['b31' => 7],
        'research' => ['r113' => 5, 'r110' => 5]
    ],
    GID_R_COMBUST_DRIVE => [
        'buildings' => ['b31' => 1],
        'research' => ['r113' => 1]
    ],
    GID_R_IMPULSE_DRIVE => [
        'buildings' => ['b31' => 2],
        'research' => ['r113' => 1]
    ],
    GID_R_HYPER_DRIVE => [
        'buildings' => ['b31' => 7],
        'research' => ['r114' => 3]
    ],
    GID_R_LASER_TECH => [
        'buildings' => ['b31' => 1],
        'research' => ['r113' => 2]
    ],
    GID_R_ION_TECH => [
        'buildings' => ['b31' => 4],
        'research' => ['r120' => 5, 'r113' => 4]
    ],
    GID_R_PLASMA_TECH => [
        'buildings' => ['b31' => 4],
        'research' => ['r113' => 8, 'r120' => 10, 'r121' => 5]
    ],
    GID_R_IGN => [
        'buildings' => ['b31' => 10],
        'research' => ['r108' => 8, 'r114' => 8]
    ],
    GID_R_EXPEDITION => [
        'buildings' => ['b31' => 3],
        'research' => ['r106' => 4, 'r117' => 3]
    ],
    GID_R_GRAVITON => [
        'buildings' => ['b31' => 12]
    ],
];

function checkRequirements(int $id, array $user, array $planet): array
{
    global $buildingRequirements, $shipyardRequirements, $researchRequirements;

    $result = [
        'met' => true,
        'missing' => [],
    ];

    // Select requirements array
    if (isBuilding($id)) {
        $requirements = $buildingRequirements[$id] ?? [];
    } elseif (isFleet($id) || isDefense($id)) {
        $requirements = $shipyardRequirements[$id] ?? [];
    } elseif (isResearch($id)) {
        $requirements = $researchRequirements[$id] ?? [];
    } else {
        $requirements = [];
    }

    // Closure to check a requirements column set (e.g. 'buildings' or 'research').
    // Parameters:
    //  - $reqs: associative array with keys like 'b21' or 'r115' and required levels
    //  - $source: array representing current object levels (planet or user)
    //  - $prefix: optional string prefix for keys (e.g. 'b' or 'r') used to extract numeric GID
    $check = function (array $reqs, array $source, string $prefix = '') use (&$result) {
        foreach ($reqs as $column => $required) {
            $current = $source[$column] ?? 0;
            if ($current < $required) {
                // Extract the numeric id in a robust way. If a prefix is provided
                // (e.g. 'b' for buildings, 'r' for research), strip it; otherwise
                // sanitize to retrieve digits.
                if ($prefix !== '') {
                    $gid = (int) substr($column, strlen($prefix));
                } else {
                    $gid = (int) preg_replace('/\D/', '', $column);
                }
                $result['met'] = false;
                $result['missing'][] = $gid;
            }
        }
    };

    if (!empty($requirements['buildings'])) {
        $check($requirements['buildings'], $planet, 'b');
    }
    if (!empty($requirements['research'])) {
        $check($requirements['research'], $user, 'r');
    }

    return $result;
}