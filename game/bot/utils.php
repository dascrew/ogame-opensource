<?php

// Bot Utility Functions - Helper functions for bot system

// Require project files relative to this file to avoid include_path/working dir issues
require_once __DIR__ . '/../id.php';
require_once __DIR__ . '/../requirements.php';
require_once __DIR__ . '/../queue.php';
require_once __DIR__ . '/../planet.php';
require_once __DIR__ . '/../debug.php';

/**
 * Maps string keys to GIDs for tech/building checks.
 *
 * @param string $key The key to map
 * @return int|null The GID or null if not found
 */
function getGIDByKey($key)
{
    static $map = [
        // Buildings
        'metal' => GID_B_METAL_MINE,
        'crystal' => GID_B_CRYS_MINE,
        'deuterium' => GID_B_DEUT_SYNTH,
        'solar' => GID_B_SOLAR,
        'fusion' => GID_B_FUSION,
        'robotics' => GID_B_ROBOTS,
        'nanite' => GID_B_NANITES,
        'shipyard' => GID_B_SHIPYARD,
        'metalstor' => GID_B_METAL_STOR,
        'crystalstor' => GID_B_CRYS_STOR,
        'deutstor' => GID_B_DEUT_STOR,
        'reslab' => GID_B_RES_LAB,
        'terraformer' => GID_B_TERRAFORMER,
        'acs' => GID_B_ALLY_DEPOT,
        'silo' => GID_B_MISS_SILO,
        'lunarbase' => GID_B_LUNAR_BASE,
        'phalanx' => GID_B_PHALANX,
        'jumpgate' => GID_B_JUMP_GATE,
        // Research
        'energy' => GID_R_ENERGY,
        'computer' => GID_R_COMPUTER,
        'weapons' => GID_R_WEAPON,
        'shield' => GID_R_SHIELD,
        'armour' => GID_R_ARMOUR,
        'hyperspace' => GID_R_HYPERSPACE,
        'combustion' => GID_R_COMBUST_DRIVE,
        'impulse' => GID_R_IMPULSE_DRIVE,
        'hyperdrive' => GID_R_HYPER_DRIVE,
        'laser' => GID_R_LASER_TECH,
        'ion' => GID_R_ION_TECH,
        'plasma' => GID_R_PLASMA_TECH,
        'ign' => GID_R_IGN,
        'expedition' => GID_R_EXPEDITION,
        'graviton' => GID_R_GRAVITON,
    ];
    $key = strtolower($key);
    return $map[$key] ?? null;
}

/**
 * Checks if a tech/building is unlocked for the user/planet by string key.
 *
 * @param array $user User data
 * @param array $planet Planet data
 * @param string $key String key for the tech/building
 * @return bool True if unlocked, false otherwise
 */
function hasTechUnlocked($user, $planet, $key)
{
    $gid = getGIDByKey($key);
    if ($gid === null) {
        return false;
    }
    $result = checkRequirements($gid, $user, $planet);
    return $result['met'];
}

/**
 * Retrieves the finish time of the first building in the queue for the given planet.
 *
 * @param mixed $planetId The ID of the planet (will be cast to int internally).
 * @return int|null The finish time as a Unix timestamp, or null if the queue is empty.
 */
function getBuildingFinishTime(mixed $planetId): ?int
{
    global $BotNow;
    $queue = GetBuildQueue((int) $planetId);
    $firstItem = dbarray($queue);
    if (empty($firstItem['end']) || $firstItem['end'] <= $BotNow) {
        return null;
    }
    return $firstItem['end'];
}/**
 * Retrieves the finish time of the current research in the queue for the given user.
 *
 * @param mixed $playerId The ID of the user (will be cast to int internally).
 * @return int|null The finish time as a Unix timestamp, or null if no research is active.
 */
function getResearchFinishTime(mixed $playerId): ?int
{
    global $BotNow;
    $queue = GetResearchQueue((int) $playerId);
    $firstItem = dbarray($queue);
    if (empty($firstItem['end']) || $firstItem['end'] <= $BotNow) {
        return null;
    }
    return $firstItem['end'];
}

/**
 * Determines the building most in need of upgrade based on ratios for a given planet.
 *
 * @param array $ratios Associative array of building GIDs to target ratios.
 * @return int|null Returns the GID of the building to build, or null if none found.
 */
/**
 * Determines the upgrade (building or research) most in need based on ratios for a given planet.
 *
 * @param array $ratios Associative array of upgrade GIDs to target ratios.
 * @return int|null Returns the GID of the upgrade to build/research, or null if none found.
 */
function getMostNeededUpgrade($ratios)
{
    $maxNeed = -1;
    $upgradeToDo = null;

    foreach ($ratios as $gid => $ratio) {
        if (isResearch($gid)) {
            $currentLevel = BotGetResearch($gid);
        } else {
            $currentLevel = BotGetBuild($gid, $planet);
        }
        $need = $ratio / ($currentLevel + 1);
        if ($need > $maxNeed) {
            $maxNeed = $need;
            $upgradeToDo = $gid;
        }
    }

    return $upgradeToDo;
}

/**
 * Finds the next requirement needed to unlock a given GID.
 *
 * @param array $user User data
 * @param array $planet Planet data
 * @param int $gid The GID to check
 * @return int|null The GID of the next requirement, or null if already unlocked
 */
function getNextForUnlock($user, $planet, $gid)
{
    $requirements = checkRequirements($gid, $user, $planet);
    if ($requirements['met']) {
        return null;
    }

    return $requirements['missing'][0] ?? null;
}

/**
 * Attempts to upgrade a building or research and returns the wait time until completion.
 *
 * @param mixed $planetId DEPRECATED - not used, kept for backward compatibility
 * @param mixed $gid The GID of the building or research to upgrade (will be cast to int internally).
 * @return int The wait time in seconds until the upgrade is complete.
 */
function upgradeAndWait(mixed $gid): int
{
    global $BotNow, $BotID;

    $gid = (int) $gid;

    $delay = rand(10, 150);
    $finish = null;

    // Get the active planet for finish time checking
    $user = LoadUser($BotID);
    $activePlanetId = $user['aktplanet'];

    if (isBuilding($gid)) {
        BotBuild($gid);
        $finish = getBuildingFinishTime($activePlanetId);
    } elseif (isResearch($gid)) {

        BotResearch($gid);
        $finish = getResearchFinishTime($activePlanetId);
    }

    if ($finish) {
        $wait = ($finish - $BotNow) + $delay;
        BotDebug("upgradeAndWait: Finish time set, waiting {$wait}s", 'INFO');
        return $wait;
    } else {
        BotDebug("upgradeAndWait: No finish time, returning default delay {$delay}s", 'INFO');
        return $delay;
    }
}
