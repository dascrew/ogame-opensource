<?php

include_once 'requirements.php';
// Auxiliary functions for the economic part of OGame.

// Calculation of cost, build time and required conditions.

// Level 1 cost.
// Factor in the exponential growth of technology. OGame is a game of exponential.
$initial = [      // m, k, d, e, factor
    // Buildings
    GID_B_METAL_MINE => [60, 15, 0, 0, 1.5],
    GID_B_CRYS_MINE => [48, 24, 0, 0, 1.6],
    GID_B_DEUT_SYNTH => [225, 75, 0, 0, 1.5],
    GID_B_SOLAR => [75, 30, 0, 0, 1.5],
    GID_B_FUSION => [900, 360, 180, 0, 1.8],
    GID_B_ROBOTS => [400, 120, 200, 0, 2],
    GID_B_NANITES => [1000000, 500000, 100000, 0, 2],
    GID_B_SHIPYARD => [400, 200, 100, 0, 2],
    GID_B_METAL_STOR => [2000, 0, 0, 0, 2],
    GID_B_CRYS_STOR => [2000, 1000, 0, 0, 2],
    GID_B_DEUT_STOR => [2000, 2000, 0, 0, 2],
    GID_B_RES_LAB => [200, 400, 200, 0, 2],
    GID_B_TERRAFORMER => [0, 50000, 100000, 1000, 2],
    GID_B_ALLY_DEPOT => [20000, 40000,  0, 0, 2],
    GID_B_MISS_SILO => [20000, 20000, 1000, 0, 2],
    // Moon
    GID_B_LUNAR_BASE => [20000, 40000, 20000, 0, 2],
    GID_B_PHALANX => [20000, 40000, 20000, 0, 2],
    GID_B_JUMP_GATE => [2000000, 4000000, 2000000, 0, 2],

    // Fleet
    GID_F_SC => [2000, 2000, 0, 0, 0],
    GID_F_LC => [6000, 6000, 0, 0, 0],
    GID_F_LF => [3000, 1000, 0, 0, 0],
    GID_F_HF => [6000, 4000, 0, 0, 0],
    GID_F_CRUISER => [20000, 7000, 2000, 0, 0],
    GID_F_BATTLESHIP => [45000, 15000, 0, 0, 0],
    GID_F_COLON => [10000, 20000, 10000, 0, 0],
    GID_F_RECYCLER => [10000, 6000, 2000, 0, 0],
    GID_F_PROBE => [0, 1000, 0, 0, 0],
    GID_F_BOMBER => [50000, 25000, 15000, 0, 0],
    GID_F_SAT => [0, 2000, 500, 0, 0],
    GID_F_DESTRO => [60000, 50000, 15000, 0, 0],
    GID_F_DEATHSTAR => [5000000, 4000000, 1000000, 0, 0],
    GID_F_BATTLECRUISER => [30000, 40000, 15000, 0, 0],

    // Defense
    GID_D_RL => [2000, 0, 0, 0, 0],
    GID_D_LL => [1500, 500, 0, 0, 0],
    GID_D_HL => [6000, 2000, 0, 0, 0],
    GID_D_GAUSS => [20000, 15000, 2000, 0, 0],
    GID_D_ION => [2000, 6000, 0, 0, 0],
    GID_D_PLASMA => [50000, 50000, 30000, 0, 0],
    GID_D_SDOME => [10000, 10000, 0, 0, 0],
    GID_D_LDOME => [50000, 50000, 0, 0, 0],
    GID_D_ABM => [8000, 0, 2000, 0, 0],
    GID_D_IPM => [12500, 2500, 10000, 0, 0],

    // Research
    GID_R_ESPIONAGE => [200, 1000, 200, 0, 2],
    GID_R_COMPUTER => [0, 400, 600, 0, 2],
    GID_R_WEAPON => [800, 200, 0, 0, 2],
    GID_R_SHIELD => [200, 600, 0, 0, 2],
    GID_R_ARMOUR => [1000, 0, 0, 0, 2],
    GID_R_ENERGY => [0, 800, 400, 0, 2],
    GID_R_HYPERSPACE => [0, 4000, 2000, 0, 2],
    GID_R_COMBUST_DRIVE => [400, 0, 600, 0, 2],
    GID_R_IMPULSE_DRIVE => [2000, 4000, 600, 0, 2],
    GID_R_HYPER_DRIVE => [10000, 20000, 6000, 0, 2],
    GID_R_LASER_TECH => [200, 100, 0, 0, 2],
    GID_R_ION_TECH => [1000, 300, 100, 0, 2],
    GID_R_PLASMA_TECH => [2000, 4000, 1000, 0, 2],
    GID_R_IGN => [240000, 400000, 160000, 0, 2],
    GID_R_EXPEDITION => [4000, 8000, 4000, 0, 2],
    GID_R_GRAVITON => [0, 0, 0, 300000, 3],
];

function BuildPrice($id, $lvl)
{
    global $initial;

    $factor = $initial[$id][4];
    $m = $initial[$id][0] * pow($factor, $lvl - 1);
    $k = $initial[$id][1] * pow($factor, $lvl - 1);
    $d = $initial[$id][2] * pow($factor, $lvl - 1);
    $e = $initial[$id][3] * pow($factor, $lvl - 1);

    $res = [ 'm' => $m, 'k' => $k, 'd' => $d, 'e' => $e ];
    return $res;
}

// Time to build a $id level $lvl building in seconds.
function BuildDuration($id, $lvl, $robots, $nanits, $speed)
{
    $res = BuildPrice($id, $lvl);
    $m = $res['m'];
    $k = $res['k'];
    $d = $res['d'];
    $e = $res['e'];
    $secs = floor(((($m + $k) / (2500 * (1 + $robots))) * pow(0.5, $nanits) * 60 * 60) / $speed);
    if ($secs < 1) {
        $secs = 1;
    }
    return $secs;
}

function ShipyardPrice($id)
{
    global $initial;
    $m = $initial[$id][0];
    $k = $initial[$id][1];
    $d = $initial[$id][2];
    $e = 0;
    $res = [ 'm' => $m, 'k' => $k, 'd' => $d, 'e' => $e ];
    return $res;
}

function ShipyardDuration($id, $shipyard, $nanits, $speed)
{
    $res = ShipyardPrice($id);
    $m = $res['m'];
    $k = $res['k'];
    $d = $res['d'];
    $e = $res['e'];
    $secs = floor(((($m + $k) / (2500 * (1 + $shipyard))) * pow(0.5, $nanits) * 60 * 60) / $speed);
    if ($secs < 1) {
        $secs = 1;
    }
    return $secs;
}

function ResearchPrice($id, $lvl)
{
    global $initial;

    $factor = $initial[$id][4];
    $m = $initial[$id][0] * pow($factor, $lvl - 1);
    $k = $initial[$id][1] * pow($factor, $lvl - 1);
    $d = $initial[$id][2] * pow($factor, $lvl - 1);
    $e = $initial[$id][3] * pow($factor, $lvl - 1);

    $res = [ 'm' => $m, 'k' => $k, 'd' => $d, 'e' => $e ];
    return $res;
}

function ResearchDuration($id, $lvl, $reslab, $speed)
{
    if ($id == GID_R_GRAVITON) {
        return 1;
    }
    $res = ResearchPrice($id, $lvl);
    $m = $res['m'];
    $k = $res['k'];
    $d = $res['d'];
    $e = $res['e'];
    $secs = floor((($m + $k) / (1000 * (1 + $reslab)) * 60 * 60) / $speed);
    if ($secs < 1) {
        $secs = 1;
    }
    return $secs;
}

// IGN Calculation.
// Attach +IGN laboratories of maximum level to the current laboratory.
// The output is the overall level of the "virtual" lab.
function ResearchNetwork($planetid, $id)
{
    global $db_prefix;
    $planet = GetPlanet($planetid);
    $player_id = $planet['owner_id'];
    $user = LoadUser($player_id);
    $ign = $user ['r' . GID_R_IGN];
    $reslab = $planet['b' . GID_B_RES_LAB];
    $labs = [];
    $labnum = 0;

    // List the player's planets (do not list moons and other special objects). Also skip planets that do not have lab.
    $query = 'SELECT * FROM ' . $db_prefix . "planets WHERE owner_id = $player_id AND type = " . PTYP_PLANET . ' AND b' . GID_B_RES_LAB . ' > 0';
    $result = dbquery($query);
    $pnum = dbrows($result);

    // Get all available labs sorted in descending order.
    while ($pnum--) {
        $p = dbarray($result);
        if ($p['planet_id'] == $planetid) {
            continue;
        }    // Skip the current planet.
        if (checkRequirements($id, $user, $p)['met']) {
            $labs[$labnum++] = $p['b' . GID_B_RES_LAB];
        }
    }
    rsort($labs);

    // Attach +IGN of available laboratories.
    for ($i = 0; $i < $ign && $i < $labnum; $i++) {
        $reslab += $labs[$i];
    }
    return $reslab;
}

// Return a string of durations by days, hours, minutes, seconds.
function BuildDurationFormat($seconds)
{
    $res = '';
    $days = (int) floor($seconds / (24 * 3600));
    $hours = ((int) floor($seconds / 3600)) % 24;
    $mins = ((int) floor($seconds / 60)) % 60;
    $secs = (int) round($seconds / 1 % 60);
    if ($days) {
        $res .= "$days" . loca('TIME_DAYS') . ' ';
    }
    if ($hours || $days) {
        $res .= "$hours" . loca('TIME_HOUR') . ' ';
    }
    if ($mins || $days) {
        $res .= "$mins" . loca('TIME_MIN') . ' ';
    }
    if ($secs) {
        $res .= "$secs" . loca('TIME_SEC');
    }
    return $res;
}

function IsEnoughResources($planet, $m, $k, $d, $e)
{
    if ($m && $planet['m'] < $m) {
        return false;
    }
    if ($k && $planet['k'] < $k) {
        return false;
    }
    if ($d && $planet['d'] < $d) {
        return false;
    }
    if ($e && $planet['emax'] < $e) {
        return false;
    }
    return true;
}

// Anything related to resource production and calculation.

// Get the size of the storages.
function store_capacity($lvl)
{
    return 100000 + 50000 * (ceil(pow(1.6, $lvl) - 1));
}

// Energy production
function prod_solar($lvl, $pr)
{
    $prod = floor(20 * $lvl * pow(1.1, $lvl) * $pr);
    return $prod;
}
function prod_fusion($lvl, $energo, $pr)
{
    $prod = floor(30 * $lvl * pow(1.05 + $energo * 0.01, $lvl) * $pr);
    return $prod;
}
function prod_sat($maxtemp)
{
    $prod = floor(($maxtemp / 4) + 20);
    return max(1, $prod);
}

// Mines production
function prod_metal($lvl, $pr)
{
    return floor(30 * $lvl * pow(1.1, $lvl) * $pr);
}
function prod_crys($lvl, $pr)
{
    return floor(20 * $lvl * pow(1.1, $lvl) * $pr);
}
function prod_deut($lvl, $maxtemp, $pr)
{
    return floor(10 * $lvl * pow(1.1, $lvl) * $pr) * (1.28 - 0.002 * ($maxtemp));
}

// Energy consumption
function cons_metal($lvl)
{
    return ceil(10 * $lvl * pow(1.1, $lvl));
}
function cons_crys($lvl)
{
    return ceil(10 * $lvl * pow(1.1, $lvl));
}
function cons_deut($lvl)
{
    return ceil(20 * $lvl * pow(1.1, $lvl));
}

// Consumption of deuterium by the fusion reactor
function cons_fusion($lvl, $pr)
{
    return ceil(10 * $lvl * pow(1.1, $lvl) * $pr) ;
}

// Check if the storage is full.
function isStorageFull($planet, $type = null)
{
    if (!is_array($planet) || !is_string($type)) {
        return false;
    }
    switch ($type) {
        case 'metal':
            return $planet['m'] >= $planet['mmax'];
        case 'crystal':
            return $planet['k'] >= $planet['kmax'];
        case 'deuterium':
            return $planet['d'] >= $planet['dmax'];
        default:
            return false;
    }
}

// Calculate resource production increase. Limit storage capacity.
// NOTE: The calculation excludes external events, such as the end of officers' actions, attack of another player, completion of building construction, etc.
function ProdResources(&$planet, $time_from, $time_to)
{
    global $db_prefix, $GlobalUni;
    if ($planet['type'] != PTYP_PLANET) {
        return;
    } // NOT a planet
    $user = LoadUser($planet['owner_id']);
    if ($user['player_id'] == USER_SPACE) {
        return; // technical account space
    }
    $diff = $time_to - $time_from;

    $speed = $GlobalUni['speed'];

    $prem = PremiumStatus($user);
    $g_factor = $prem['geologist'] ? 1.1 : 1.0;

    // Metal production
    $hourly = prod_metal($planet['b1'], $planet['mprod']) * $planet['factor'] * $speed * $g_factor + 20 * $speed;
    if (!isStorageFull($planet, 'metal')) {
        $planet['m'] += ($hourly * $diff) / 3600;
        if (isStorageFull($planet, 'metal')) {
            $planet['m'] = $planet['mmax'];
        }
    }

    // Crystal production
    $hourly = prod_crys($planet['b2'], $planet['kprod']) * $planet['factor'] * $speed * $g_factor + 10 * $speed;
    if (!isStorageFull($planet, 'crystal')) {
        $planet['k'] += ($hourly * $diff) / 3600;
        if (isStorageFull($planet, 'crystal')) {
            $planet['k'] = $planet['kmax'];
        }
    }

    // Deuterium production
    $hourly = prod_deut($planet['b3'], $planet['temp'] + 40, $planet['dprod']) * $planet['factor'] * $speed * $g_factor;
    $hourly -= cons_fusion($planet['b12'], $planet['fprod']) * $speed; // Fusion reactor consumption
    if (!isStorageFull($planet, 'deuterium')) {
        $planet['d'] += ($hourly * $diff) / 3600;
        if (isStorageFull($planet, 'deuterium')) {
            $planet['d'] = $planet['dmax'];
        }
    }

    // Update database
    $planet_id = $planet['planet_id'];
    $query = 'UPDATE ' . $db_prefix . "planets SET m = '" . $planet['m'] . "', k = '" . $planet['k'] . "', d = '" . $planet['d'] . "', lastpeek = '" . $time_to . "' WHERE planet_id = $planet_id";
    dbquery($query);
    $planet['lastpeek'] = $time_to;
}

// The cost of the planet in points.
function PlanetPrice($planet)
{
    $pp = [];
    global $buildmap;
    global $fleetmap;
    global $defmap;

    $m = $k = $d = $e = 0;
    $pp['points'] = $pp['fpoints'] = $pp['fleet_pts'] = $pp['defense_pts'] = 0;

    foreach ($buildmap as $i => $gid) {        // Buildings
        $level = $planet["b$gid"];
        if ($level > 0) {
            for ($lv = 1; $lv <= $level; $lv++) {
                $res = BuildPrice($gid, $lv);
                $m = $res['m'];
                $k = $res['k'];
                $d = $res['d'];
                $e = $res['e'];
                $pp['points'] += ($m + $k + $d);
            }
        }
    }

    foreach ($fleetmap as $i => $gid) {        // Fleet
        $level = $planet["f$gid"];
        if ($level > 0) {
            $res = ShipyardPrice($gid);
            $m = $res['m'];
            $k = $res['k'];
            $d = $res['d'];
            $e = $res['e'];
            $pp['points'] += ($m + $k + $d) * $level;
            $pp['fleet_pts'] += ($m + $k + $d) * $level;
            $pp['fpoints'] += $level;
        }
    }

    foreach ($defmap as $i => $gid) {        // Defense
        $level = $planet["d$gid"];
        if ($level > 0) {
            $res = ShipyardPrice($gid);
            $m = $res['m'];
            $k = $res['k'];
            $d = $res['d'];
            $e = $res['e'];
            $pp['points'] += ($m + $k + $d) * $level;
            $pp['defense_pts'] += ($m + $k + $d) * $level;
        }
    }

    return $pp;
}

// Fleet cost
function FleetPrice($fleet_obj)
{
    global $fleetmap;
    $m = $k = $d = $e = 0;
    $points = $fpoints = 0;
    $price = [];

    foreach ($fleetmap as $i => $gid) {        // Fleet
        $level = $fleet_obj["ship$gid"];
        if ($level > 0) {
            $res = ShipyardPrice($gid);
            $m = $res['m'];
            $k = $res['k'];
            $d = $res['d'];
            $e = $res['e'];
            $points += ($m + $k + $d) * $level;
            $fpoints += $level;
        }
    }

    $price['points'] = $points;
    $price['fpoints'] = $fpoints;
    return $price;
}