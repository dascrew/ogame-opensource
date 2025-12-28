<?php

declare(strict_types=1);

// Resolve includes relative to this file to avoid include_path issues
include_once __DIR__ . '/../id.php';
include_once __DIR__ . '/api.php';
include_once __DIR__ . '/utils.php';
include_once __DIR__ . '/../prod.php';
include_once __DIR__ . '/../requirements.php';
include_once __DIR__ . '/debug.php';

/**
 * Get the macro map that maps macro names to their handler functions.
 *
 * @return array<string, string> Map of macro names to function names
 */
function getMacroMap(): array
{
    return [
        'mines' => 'buildMines',
        'basic' => 'buildBase',
        'tech' => 'buildForTech',
        'research' => 'researchFromPersonality',
        'shipyard' => 'buildMacro',
        'robotics' => 'buildMacro',
        'reslab' => 'buildMacro',
        'nanite' => 'buildMacro',
        'fusion' => 'buildMacro',
        'terraformer' => 'buildMacro',
        'silo' => 'buildMacro',
        'metal' => 'buildMacro',
        'crystal' => 'buildMacro',
        'deuterium' => 'buildMacro',
        'metalstor' => 'buildMacro',
        'crystalstor' => 'buildMacro',
        'deutstor' => 'buildMacro',
        'solar' => 'buildMacro',
        'acs' => 'buildMacro',
        'speed' => 'buildSpeedInfrastructure',
        'build' => 'buildFromPersonality',
        'sleep' => 'sleepDelay',
        'basicres' => 'researchBase',
    ];
}

/**
 * Macro for sleep/delay. Returns the number of seconds to sleep.
 *
 * @param int $seconds Number of seconds to sleep (default 8 hours)
 * @return int The number of seconds to sleep
 */
function sleepDelay(int $seconds = 28800): int
{
    return $seconds;
}

// Main Functions

/**
 * Attempts to build essential base structures on the given planet.
 *
 * Checks for an active build queue and sufficient energy before building.
 * Prioritizes building a Solar Plant if energy is insufficient.
 * Builds the first available target building (Metal Mine, Crystal Mine, Deuterium Synthesizer, Research Lab)
 * that is below its target level.
 *
 * @param array<string, mixed> $aktplanet Associative array containing planet data. Must include 'planet_id'.
 * @return int Delay in seconds before next attempt
 */
function buildBase(array $aktplanet): int
{
    global $BotNow;
    $finishTime = getBuildingFinishTime($aktplanet['planet_id']);
    if ($finishTime) {
        BotDebug("buildMacro: build queue busy on planet {$aktplanet['planet_id']} until {$finishTime}", 'INFO');
        return $finishTime - $BotNow;
    }
    if (!BotEnergyAbove(0) && BotCanBuild(GID_B_SOLAR)) {
        return upgradeAndWait(GID_B_SOLAR);
    }

    $targets = [
        GID_B_METAL_MINE => 4,
        GID_B_CRYS_MINE => 2,
        GID_B_DEUT_SYNTH => 1,
        GID_B_RES_LAB => 1,
    ];

    foreach ($targets as $gid => $level) {
        if (BotGetBuild($gid) < $level && BotCanBuild($gid)) {
            return upgradeAndWait($gid);
        }
    }
    return 14400;
}

/**
 * Attempts to optimize build speed by upgrading Robotics Factory or Nanite Factory if they reduce build time.
 *
 * @param int $gid The building GID to build
 * @param array<string, mixed> $aktplanet The current planet data
 * @return int Delay in seconds before next attempt
 */
function buildForSpeed(int $gid, array $aktplanet): int
{
    $unitab = LoadUniverse();
    $speed = (is_array($unitab) && isset($unitab['speed'])) ? $unitab['speed'] : 1;

    $roboticsLevel = BotGetBuild(GID_B_ROBOTS);
    $nanitesLevel = BotGetBuild(GID_B_NANITES);
    $targetLevel = BotGetBuild($gid) + 1;

    $futureRoboticsDuration = (int) floor(BuildDuration($gid, $targetLevel, $roboticsLevel + 1, $nanitesLevel, $speed));
    $futureNanitesDuration = (int) floor(BuildDuration($gid, $targetLevel, $roboticsLevel, $nanitesLevel + 1, $speed));
    $targetDuration = (int) floor(BuildDuration($gid, $targetLevel, $roboticsLevel, $nanitesLevel, $speed));

    if ($futureRoboticsDuration < $targetDuration && BotCanBuild(GID_B_ROBOTS)) {
        return upgradeAndWait(GID_B_ROBOTS);
    }

    if ($futureNanitesDuration < $targetDuration && BotCanBuild(GID_B_NANITES)) {
        return upgradeAndWait(GID_B_NANITES);
    }

    if (BotCanBuild($gid)) {
        return upgradeAndWait($gid);
    }
    return rand(10, 150);
}

/**
 * Attempts to build or upgrade mines (Metal, Crystal, Deuterium) on the specified planet.
 *
 * Waits if there is an active build queue. Builds Solar Plant if energy is insufficient.
 * Builds the first available mine that can be constructed.
 *
 * @param array<string, mixed> $aktplanet Associative array containing planet data, must include 'planet_id'.
 * @return int Delay in seconds before next attempt
 */
function buildMines(array $aktplanet): int
{
    global $BotNow;
    if (getBuildingFinishTime($aktplanet['planet_id'])) {
        return getBuildingFinishTime($aktplanet['planet_id']) - $BotNow;
    }

    if (!BotEnergyAbove(0)) {
        if (BotCanBuild(GID_B_SOLAR)) {
            return upgradeAndWait(GID_B_SOLAR);
        } else {
            return rand(10, 150);
        }
    }

    foreach ([GID_B_METAL_MINE, GID_B_CRYS_MINE, GID_B_DEUT_SYNTH] as $gid) {
        if (BotCanBuild($gid)) {
            return upgradeAndWait($gid);
        }
    }
    return rand(10, 150);
}


/**
 * Generic builder for simple buildings like Shipyard and Robotics Factory.
 *
 * @param array<string, mixed> $aktplanet Associative array containing planet data, must include 'planet_id'.
 * @param int|null $gid Optional GID to build. If not provided, will infer from macro name.
 * @param string|null $macroName Optional macro name for context (used by macro dispatcher).
 * @return int Delay in seconds before next attempt
 */
function buildMacro(array $aktplanet, ?int $gid = null, ?string $macroName = null): int
{
    global $BotNow, $BotID;

    if (getBuildingFinishTime($aktplanet['planet_id'])) {
        return getBuildingFinishTime($aktplanet['planet_id']) - $BotNow;
    }

    if ($gid === null && $macroName !== null) {
        $gid = getGIDByKey($macroName);
        if ($gid !== null) {
            BotDebug("Resolved macro '$macroName' to GID: $gid", 'INFO');
        } else {
            BotDebug("Failed to resolve macro '$macroName' to GID", 'ERROR');
        }
    }

    if ($gid === null) {
        BotDebug('buildMacro called with null GID and no macroName', 'ERROR');
        return rand(10, 30); // Unknown building
    }

    // Debug: log context before attempting the build
    $currentLevel = BotGetBuild($gid);
    $resM = $aktplanet['m'] ?? ($aktplanet['metal'] ?? 'n/a');
    $resK = $aktplanet['k'] ?? ($aktplanet['crystal'] ?? 'n/a');
    $resD = $aktplanet['d'] ?? ($aktplanet['deuterium'] ?? 'n/a');
    BotDebug(
        "buildMacro: gid=$gid planet={$aktplanet['planet_id']} level={$currentLevel} res M:$resM K:$resK D:$resD",
        'INFO'
    );
    BotDebug(
        'buildMacro planet snapshot: ' . json_encode([
            'keys' => array_keys($aktplanet),
            'm' => $aktplanet['m'] ?? null,
            'k' => $aktplanet['k'] ?? null,
            'd' => $aktplanet['d'] ?? null,
            'metal' => $aktplanet['metal'] ?? null,
            'crystal' => $aktplanet['crystal'] ?? null,
            'deuterium' => $aktplanet['deuterium'] ?? null,
        ]),
        'INFO'
    );

    if (BotCanBuild($gid)) {
        BotDebug("buildMacro: queueing GID {$gid} on planet {$aktplanet['planet_id']}", 'ACTION');
        return upgradeAndWait($gid);
    }

    BotDebug("buildMacro: BotCanBuild returned false for GID {$gid} on planet {$aktplanet['planet_id']}", 'INFO');
    return rand(10, 30);
}

/**
 * Attempts to build storage facilities (Metal, Crystal, Deuterium) if any resource storage is full.
 *
 * Checks if there is an active build queue before initiating construction of the corresponding
 * storage building for any full resource storage.
 *
 * @param array<string, mixed> $aktplanet Associative array containing planet data, must include resource amounts and 'planet_id'.
 * @return int Delay in seconds before next attempt
 */
function buildStorage(array $aktplanet): int
{
    if (getBuildingFinishTime($aktplanet['planet_id'])) {
        return getBuildingFinishTime($aktplanet['planet_id']) - time() + rand(10, 30);
    }

    if (!isStorageFull($aktplanet, 'metal') &&
        !isStorageFull($aktplanet, 'crystal') &&
        !isStorageFull($aktplanet, 'deuterium')) {
        return rand(10, 30);
    }

    $storages = [
        ['gid' => GID_B_METAL_STOR, 'type' => 'metal'],
        ['gid' => GID_B_CRYS_STOR, 'type' => 'crystal'],
        ['gid' => GID_B_DEUT_STOR, 'type' => 'deuterium'],
    ];

    foreach ($storages as $storage) {
        if (isStorageFull($aktplanet, $storage['type']) && BotCanBuild($storage['gid'])) {
            return upgradeAndWait($storage['gid']);
        }
    }
    return rand(10, 30);
}

/**
 * Attempts to build structures based on the bot's personality configuration.
 *
 * Checks if the build queue is empty, iterates through upgrade priorities, and determines
 * the most needed building based on defined ratios. Initiates the build process for speed.
 *
 * @param array<string, mixed> $aktplanet The current planet's data, must include 'planet_id'.
 * @return int Delay in seconds before next attempt
 */
function buildFromPersonality(array $aktplanet): int
{
    global $BotID;
    $delay = rand(10, 30);

    // Load personality configuration
    $personalityName = BotGetVar('personality', '');
    if (empty($personalityName)) {
        BotDebug("No personality set for bot", 'WARNING');
        return $delay;
    }
    
    $personality = loadPersonalityConfig($personalityName);
    if (!$personality) {
        BotDebug("Failed to load personality config: {$personalityName}", 'ERROR');
        return $delay;
    }

    // If a building is already in progress, wait until it finishes
    if (getBuildingFinishTime($aktplanet['planet_id'])) {
        return getBuildingFinishTime($aktplanet['planet_id']) - time() + rand(10, 30);
    }
    $category = 'buildings';
    if (!isset($personality['upgrade_priority'][$category]) || !isset($personality[$category]['ratios'])) {
        return $delay;
    }
    foreach ($personality['upgrade_priority'][$category] as $subcat) {
        if (!isset($personality[$category]['ratios'][$subcat])) {
            continue;
        }
        $ratios = $personality[$category]['ratios'][$subcat];
        $toBuild = getMostNeededUpgrade($ratios);
        if ($toBuild && BotCanBuild($toBuild)) {
            return buildForSpeed($toBuild, $aktplanet);
        }
    }
    // If nothing to build, wait before retrying
    return $delay;
}

/**
 * Attempts to research technologies based on the bot's personality configuration.
 *
 * Checks if the research queue is empty, iterates through upgrade priorities, and determines
 * the most needed research based on defined ratios. Initiates the research process.
 *
 * @param array<string, mixed> $aktplanet The current planet's data, must include 'owner_id'.
 * @return int Delay in seconds before next attempt
 */
function researchFromPersonality(array $aktplanet): int
{
    global $BotID, $BotNow;

    $delay = rand(10, 30);

    // Load personality configuration
    $personalityName = BotGetVar('personality', '');
    if (empty($personalityName)) {
        BotDebug("No personality set for bot", 'WARNING');
        return $delay;
    }
    
    $personality = loadPersonalityConfig($personalityName);
    if (!$personality) {
        BotDebug("Failed to load personality config: {$personalityName}", 'ERROR');
        return $delay;
    }

    if (getResearchFinishTime($aktplanet['owner_id'])) {
        return getResearchFinishTime($aktplanet['owner_id']) - $BotNow + $delay;
    }

    $category = 'research';
    if (!isset($personality['upgrade_priority'][$category]) || !isset($personality[$category]['ratios'])) {
        return $delay;
    }

    $researchTime = null;
    $user = LoadUser($BotID);

    if ($user) {
        foreach ($personality['upgrade_priority'][$category] as $subcat) {
            if (!isset($personality[$category]['ratios'][$subcat])) {
                continue;
            }

            $ratios = $personality[$category]['ratios'][$subcat];
            $target = getMostNeededUpgrade($ratios);

            if (!$target || !isResearch($target)) {
                continue;
            }

            if (BotCanResearch($target)) {
                BotResearch($target);
                $researchTime = getResearchFinishTime($aktplanet['owner_id']) - $BotNow + $delay;
                break;
            }
        }
    }

    return $researchTime ?? $delay;
}


/**
 * Attempts to build buildings or research technologies needed to unlock advanced structures.
 *
 * Checks requirements for key buildings (Shipyard, Missile Silo, Fusion Reactor, Nanite Factory, Terraformer)
 * and builds missing prerequisites.
 *
 * @param array<string, mixed> $aktplanet The current planet's data, must include 'planet_id' and 'owner_id'.
 * @return int Delay in seconds before next attempt
 */
function buildForTech(array $aktplanet): int
{
    global $BotID, $BotNow;

    $user = LoadUser($BotID);
    $delay = rand(10, 30);
    if (!$user) {
        return $delay;
    }

    $buildFinish = getBuildingFinishTime($aktplanet['planet_id']);
    $researchFinish = isset($aktplanet['owner_id']) ? getResearchFinishTime($aktplanet['owner_id']) : null;

    if ($buildFinish && $researchFinish) {
        return max($buildFinish, $researchFinish) - $BotNow + $delay;
    }

    $buildunlocks = [GID_B_SHIPYARD, GID_B_MISS_SILO, GID_B_FUSION, GID_B_NANITES, GID_B_TERRAFORMER];
    foreach ($buildunlocks as $gid) {
        $next = getNextForUnlock($user, $aktplanet, $gid);
        if (!$next) {
            continue;
        }

        if (isBuilding($next)) {
            if (!$buildFinish && BotCanBuild($next)) {
                return upgradeAndWait($next);
            }
            if ($buildFinish) {
                return $buildFinish - $BotNow + $delay;
            }
        }

        if (isResearch($next)) {
            if (!$researchFinish && BotCanResearch($next)) {
                if (isset($aktplanet['owner_id'])) {
                    return upgradeAndWait($next);
                } else {
                    // Fallback or error handling if owner_id is missing
                    return $delay;
                }
            }
            if ($researchFinish) {
                return $researchFinish - $BotNow + $delay;
            }
        }
        break;
    }
    return $delay;
}

function researchBase(array $aktplanet): int
{
    global $BotNow;
    $delay = rand(10, 150);
    if (getResearchFinishTime($aktplanet['owner_id'])) {
        return getResearchFinishTime($aktplanet['owner_id']) - $BotNow;
    }
    $targets = [
        GID_R_ENERGY => 3,
        GID_R_COMBUST_DRIVE => 3,
        GID_R_IMPULSE_DRIVE => 3,
        GID_R_ESPIONAGE => 2,
        GID_R_COMPUTER => 2,
    ];

    foreach ($targets as $gid => $level) {
        if (BotGetResearch($gid) < $level && BotCanResearch($gid)) {
            BotResearch($gid);
            return upgradeAndWait($gid);
        }
    }
    return $delay;
}

function researchMacro(array $aktplanet, ?int $gid = null, ?string $macroName = null): int
{
    global $BotNow;
    $delay = rand(10, 30);

    if (getResearchFinishTime($aktplanet['owner_id'])) {
        return getResearchFinishTime($aktplanet['owner_id']) - $BotNow + $delay;
    }

    if ($gid === null && $macroName !== null) {
        $gid = getGIDByKey($macroName);
        if ($gid !== null) {
            BotDebug("Resolved macro '$macroName' to GID: $gid", 'INFO');
        } else {
            BotDebug("Failed to resolve macro '$macroName' to GID", 'ERROR');
        }
    }

    if ($gid === null) {
        BotDebug('researchMacro called with null GID and no macroName', 'ERROR');
        return $delay; // Unknown research
    }

    if (BotCanResearch($gid)) {
        BotDebug("Researching GID $gid for player {$aktplanet['owner_id']}", 'ACTION');
        return upgradeAndWait($gid);
    }

    BotDebug("Cannot research GID $gid (requirements not met)", 'INFO');
    return $delay;
}
