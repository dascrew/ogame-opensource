<?php

// Require files relative to this file to ensure correct resolution
require_once __DIR__ . '/../id.php';
require_once __DIR__ . '/../requirements.php';
require_once __DIR__ . '/api.php';
require_once __DIR__ . '/utils.php';
require_once __DIR__ . '/../planet.php';
require_once __DIR__ . '/../queue.php';
require_once __DIR__ . '/../prod.php';
require_once __DIR__ . '/../fleet.php';
/**
 * Get the condition mapping for the visual designer.
 * Maps keywords to condition check function names.
 *
 * @return array Associative array of keyword => function_name
 */
function getConditionMap()
{
    return [
        'basicdone' => 'isBasicDone',
        'hasnanitetech' => 'hasNaniteFactoryTech',
        'hasnanitefactory' => 'hasNaniteFactoryTech',
        'allships' => 'hasAllPersonalityShips',
        'hasallships' => 'hasAllPersonalityShips',
        'personalityships' => 'hasAllPersonalityShips',
        'canexpedition' => 'canCoverExpeditionCost',
        'idle' => 'shouldIdle',
        'allbuildings' => 'hasAllBuildings',
        'basicresearch' => 'hasBasicResearch',
    ];
}

function isBasicDone()
{
    $metal_mine_level = BotGetBuild(GID_B_METAL_MINE);
    $crystal_mine_level = BotGetBuild(GID_B_CRYS_MINE);
    $deut_synth_level = BotGetBuild(GID_B_DEUT_SYNTH);
    $lab_level = BotGetBuild(GID_B_RES_LAB);

    return $metal_mine_level >= 4 && $crystal_mine_level >= 2 && $deut_synth_level >= 1 && $lab_level >= 1;
}

//a function to check if the bot has unlocked the nanite factory technology
function hasNaniteFactoryTech($botID)
{

    $user = LoadUser($botID);
    $planet = GetPlanet($user['aktplanet']);
    $result = checkRequirements(GID_B_NANITES, $user, $planet);
    return $result['met'];
}

//a function to check if the bot has unlocked all of the ships in their personality config
function hasAllPersonalityShips($botID)
{
    $user = LoadUser($botID);
    $planet = GetPlanet($user['aktplanet']);
    
    // Load personality configuration
    $personalityName = GetVar($botID, 'personality', '');
    if (empty($personalityName)) {
        BotDebug("No personality set for bot {$botID}", 'WARNING');
        return false;
    }
    
    $personality = loadPersonalityConfig($personalityName);
    if (!$personality || !isset($personality['ships']['ratios'])) {
        BotDebug("Failed to load personality config or no ships defined: {$personalityName}", 'ERROR');
        return false;
    }

    // Extract all ship GIDs from the nested ratios structure
    $allShips = [];
    foreach ($personality['ships']['ratios'] as $subcategory => $ships) {
        foreach ($ships as $shipGID => $ratio) {
            $allShips[] = $shipGID;
        }
    }

    // Check if all ships are unlocked
    foreach ($allShips as $shipGID) {
        $result = checkRequirements($shipGID, $user, $planet);
        if (!$result['met']) {
            return false;
        }
    }
    return true;
}

function shouldIdle($botID = null)
{
    global $BotID;
    $id = $botID ?? $BotID;
    $user = LoadUser($id);
    $aktplanet = GetPlanet($user['aktplanet']);

    if (!BotEnergyAbove(0) && !BotCanBuild(GID_B_SOLAR)) {
        return true;
    }
    if (GetBuildQueue($aktplanet['planet_id']) && GetResearchQueue($aktplanet['planet_id'])) {
        return true;
    }
    return false;
}

/**
 * Check if bot has enough deuterium in storage to send their entire fleet
 * to the expedition position at minimum speed.
 *
 * @return bool True if can cover expedition cost
 */
function canCoverExpeditionCost()
{
    global $BotID;
    $user = LoadUser($BotID);
    $planet = GetPlanet($user['aktplanet']);
    $coords = explode(':', $planet['coords']);
    $system = $coords[1];
    $distance = FlightDistance($coords[0], $coords[1], $coords[2], $coords[0], $system, 16);
    $ships = getShipsOnPlanet($planet['planet_id']);
    $flighttime = FlightTime($distance, 1, 10, 1);
    $deut_cost = FlightCons($ships, $distance, $flighttime, $user['r115'], $user['r117'], $user['r118'], 1)['fleet'];
    return $planet['d'] >= $deut_cost;
}

/**
 * Storage check: use canonical `isStorageFull` from `game/prod.php`.
 * This file should rely on the implementation in `game/prod.php`.
 */

/**
 * Check if a building or research is at or above a certain level.
 * Syntax: "shipyard:2" checks if shipyard >= level 2.
 *
 * @param string $condition The condition string (e.g., "shipyard:2", "robotics:5")
 * @return bool|null True if condition met, false if not met, null if invalid condition
 */
function checkLevelCondition(string $condition): ?bool
{
    // Parse the condition (format: keyword:level)
    $parts = explode(':', $condition);
    if (count($parts) !== 2) {
        return null;
    }

    $keyword = strtolower(trim($parts[0]));
    $targetLevel = (int) trim($parts[1]);

    // Get the GID for this keyword
    $gid = getGIDByKey($keyword);
    if ($gid === null) {
        return null;
    }

    // Get the current level (building or research)
    if (isBuilding($gid)) {
        $currentLevel = BotGetBuild($gid);
    } elseif (isResearch($gid)) {
        $currentLevel = BotGetResearch($gid);
    } else {
        return null;
    }

    return $currentLevel >= $targetLevel;
}

/**
 * Check if the bot's personality matches a given type.
 * Syntax: "personality:miner" checks if the bot has the "miner" personality.
 *
 * @param string $condition The condition string (e.g., "personality:miner")
 * @return bool|null True if personality matches, false if not, null if invalid condition
 */
function checkPersonalityCondition(string $condition): ?bool
{
    // Parse the condition (format: personality:type)
    $parts = explode(':', $condition);
    if (count($parts) !== 2) {
        return null;
    }

    $keyword = strtolower(trim($parts[0]));
    if ($keyword !== 'personality') {
        return null;
    }

    $targetType = strtolower(trim($parts[1]));
    $currentPersonality = BotGetVar('personality', '');

    return strtolower($currentPersonality) === $targetType;
}

function hasAllBuildings($botID)
{
    global $buildmap;
    $user = LoadUser($botID);
    $planet = GetPlanet($user['aktplanet']);
    $isMoon = ($planet['type'] == PTYP_MOON);

    // Moon-only buildings
    $moonOnlyBuildings = [GID_B_LUNAR_BASE, GID_B_PHALANX, GID_B_JUMP_GATE];

    foreach ($buildmap as $buildingGID) {
        $isMoonBuilding = in_array($buildingGID, $moonOnlyBuildings);
        if ($isMoon && !$isMoonBuilding) {
            continue;
        }
        if (!$isMoon && $isMoonBuilding) {
            continue;
        }

        $result = checkRequirements($buildingGID, $user, $planet);
        if (!$result['met']) {
            return false;
        }
    }
    return true;
}

function hasBasicResearch($botID)
{
    $user = LoadUser($botID);
    $targets = [
        GID_R_ENERGY => 3,
        GID_R_COMBUST_DRIVE => 3,
        GID_R_IMPULSE_DRIVE => 3,
        GID_R_ESPIONAGE => 2,
        GID_R_COMPUTER => 2,
    ];

    foreach ($targets as $researchGID => $level) {
        if (!isset($user['r' . $researchGID]) || $user['r' . $researchGID] < $level) {
            return false;
        }
    }
    return true;
}
