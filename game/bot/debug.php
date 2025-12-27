<?php

declare(strict_types=1);

/**
 * Bot Debugging System
 *
 * Provides comprehensive logging for bot actions, conditions, and decisions.
 */

// Global bot debugging configuration
$BOT_DEBUG_ENABLED = true;  // Master switch for all bot debugging
$BOT_DEBUG_TO_FILE = true;  // Write debug messages to file
$BOT_DEBUG_FILE = __DIR__ . '/../logs/bot_debug.log';  // Debug log file path

/**
 * Check if bot debugging is enabled (globally or for specific bot)
 *
 * @return bool True if debugging is enabled
 */
function IsBotDebugEnabled(): bool
{
    global $BOT_DEBUG_ENABLED, $BotID;

    if (!$BOT_DEBUG_ENABLED) {
        return false;
    }

    // Respect per-bot toggle only when explicitly disabled (set to "0")
    if ($BotID > 0) {
        $bot_debug = BotGetVar('debug', null);
        if ($bot_debug !== null && $bot_debug !== '') {
            return $bot_debug !== '0' && $bot_debug !== 0;
        }
    }

    return true;  // Default to enabled if no explicit override
}

/**
 * Log bot debug message
 *
 * @param string $message The debug message
 * @param string $level The log level (INFO, ACTION, CONDITION, ERROR)
 */
function BotDebug(string $message, string $level = 'INFO'): void
{
    global $BOT_DEBUG_TO_FILE, $BOT_DEBUG_FILE, $BotID;

    if (!IsBotDebugEnabled()) {
        return;
    }

    // Format message with timestamp and bot info
    $timestamp = date('Y-m-d H:i:s');
    $bot_name = GetBotDebugName();
    $formatted = "[$timestamp] [$bot_name] [$level] $message\n";

    // Write to debug log file (and surface write failures to PHP error log)
    if ($BOT_DEBUG_TO_FILE && $BOT_DEBUG_FILE) {
        $log_dir = dirname($BOT_DEBUG_FILE);
        if (!is_dir($log_dir)) {
            if (!mkdir($log_dir, 0755, true) && !is_dir($log_dir)) {
                error_log("BotDebug: failed to create log directory '{$log_dir}'");
                return;
            }
        }

        if (!is_writable($log_dir)) {
            error_log("BotDebug: log directory not writable '{$log_dir}'");
            return;
        }

        $result = file_put_contents($BOT_DEBUG_FILE, $formatted, FILE_APPEND);
        if ($result === false) {
            error_log("BotDebug: failed to write to log file '{$BOT_DEBUG_FILE}'");
        }
    }

    // Also send to game debug system
    Debug("Bot[$bot_name]: $message");
}

/**
 * Get bot name for debugging
 *
 * @return string Bot username or player ID
 */
function GetBotDebugName(): string
{
    global $BotID;

    if ($BotID > 0) {
        $user = LoadUser($BotID);
        if ($user && isset($user['username'])) {
            return $user['username'];
        }
        return "ID:$BotID";
    }

    return 'Unknown';
}

/**
 * Enable debugging for a specific bot
 *
 * @param int $bot_id The bot's player ID
 */
function EnableBotDebug(int $bot_id): void
{
    SetVar($bot_id, 'debug', '1');
    BotDebug('Debug mode enabled', 'INFO');
}

/**
 * Disable debugging for a specific bot
 *
 * @param int $bot_id The bot's player ID
 */
function DisableBotDebug(int $bot_id): void
{
    SetVar($bot_id, 'debug', '0');
}

/**
 * Clear the bot debug log file
 */
function ClearBotDebugLog(): void
{
    global $BOT_DEBUG_FILE;

    if ($BOT_DEBUG_FILE && file_exists($BOT_DEBUG_FILE)) {
        @unlink($BOT_DEBUG_FILE);
    }
}

/**
 * Get the last N lines from the bot debug log
 *
 * @param int $lines Number of lines to retrieve
 * @return array Array of log lines
 */
function GetBotDebugLog(int $lines = 100): array
{
    global $BOT_DEBUG_FILE;

    if (!$BOT_DEBUG_FILE || !file_exists($BOT_DEBUG_FILE)) {
        return [];
    }

    $file = file($BOT_DEBUG_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($file === false) {
        return [];
    }

    return array_slice($file, -$lines);
}

/**
 * Log a bot action with detailed information
 *
 * @param string $action_name The action being performed
 * @param array $details Additional details about the action
 */
function BotLogAction(string $action_name, array $details = []): void
{
    $detail_str = empty($details) ? '' : ' - ' . json_encode($details);
    BotDebug("ACTION: $action_name$detail_str", 'ACTION');
}

/**
 * Log a bot condition evaluation
 *
 * @param string $condition The condition being evaluated
 * @param bool $result The result of the evaluation
 */
function BotLogCondition(string $condition, bool $result): void
{
    $result_str = $result ? 'TRUE' : 'FALSE';
    BotDebug("CONDITION: '$condition' => $result_str", 'CONDITION');
}

/**
 * Log a bot decision
 *
 * @param string $decision Description of the decision made
 * @param string $reason Reason for the decision
 */
function BotLogDecision(string $decision, string $reason): void
{
    BotDebug("DECISION: $decision (Reason: $reason)", 'INFO');
}

/**
 * Log a bot resource state
 *
 * @param array $planet Planet data with resources
 */
function BotLogResources(array $planet): void
{
    if (!IsBotDebugEnabled()) {
        return;
    }

    $metal = isset($planet['metal']) ? number_format($planet['metal'], 0) : 'N/A';
    $crystal = isset($planet['crystal']) ? number_format($planet['crystal'], 0) : 'N/A';
    $deuterium = isset($planet['deuterium']) ? number_format($planet['deuterium'], 0) : 'N/A';
    $energy = isset($planet['energy']) ? number_format($planet['energy'], 0) : 'N/A';

    BotDebug("RESOURCES: M:$metal C:$crystal D:$deuterium E:$energy", 'INFO');
}

/**
 * Format seconds into human-readable time
 *
 * @param int $seconds Number of seconds
 * @return string Formatted time string
 */
function FormatBotTime(int $seconds): string
{
    if ($seconds < 60) {
        return "{$seconds}s";
    }

    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;

    $parts = [];
    if ($hours > 0) {
        $parts[] = "{$hours}h";
    }
    if ($minutes > 0) {
        $parts[] = "{$minutes}m";
    }
    if ($secs > 0 || empty($parts)) {
        $parts[] = "{$secs}s";
    }

    return implode(' ', $parts);
}
