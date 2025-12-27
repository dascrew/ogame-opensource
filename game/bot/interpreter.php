<?php

// Bot Interpreter - Strategy block execution engine

require_once 'api.php';
require_once 'macros.php';
require_once 'conditions.php';
require_once 'debug.php';
require_once 'localized_blocks.php';

// Global bot variables.
$BotID = 0;        // ordinal number of the current bot
$BotNow = 0;       // start time of bot task execution

// Bot debugging configuration
$BOT_DEBUG_ENABLED = true;  // Master switch for all bot debugging
$BOT_DEBUG_TO_FILE = true;  // Write debug messages to file
$BOT_DEBUG_FILE = __DIR__ . '/../logs/bot_debug.log';  // Debug log file path

/**
 * Execute a bot macro action with the current planet
 *
 * @param string $function_name The macro function to execute
 * @param string|null $macro_keyword The macro keyword (e.g., 'crystal', 'metal') for buildMacro functions
 * @return bool|int Returns int for delay seconds, true for success, false for failure
 */
function ExecuteBotMacro($function_name, $macro_keyword = null)
{
    global $BotID;

    if (!function_exists($function_name)) {
        BotDebug("Macro function '$function_name' not found", 'ERROR');
        return false;
    }

    try {
        $user = LoadUser($BotID);
        if (!$user || !isset($user['aktplanet'])) {
            BotDebug("Failed to load user $BotID or planet not set", 'ERROR');
            return false;
        }

        $aktplanet = GetPlanet($user['aktplanet']);
        if (!$aktplanet) {
            BotDebug("Failed to load planet {$user['aktplanet']}", 'ERROR');
            return false;
        }

        $planet_name = $aktplanet['name'] ?? 'Unknown';
        BotDebug("Executing macro '$function_name' on planet '$planet_name'" . ($macro_keyword ? " (keyword: $macro_keyword)" : ''), 'ACTION');

        // If macro_keyword is provided (for buildMacro), pass it as the third parameter
        if ($macro_keyword !== null && $function_name === 'buildMacro') {
            $result = call_user_func($function_name, $aktplanet, null, $macro_keyword);
        } else {
            $result = call_user_func($function_name, $aktplanet);
        }

        if (is_int($result) && $result > 0) {
            $hours = floor($result / 3600);
            $minutes = floor(($result % 3600) / 60);
            $time_str = $hours > 0 ? "{$hours}h {$minutes}m" : "{$minutes}m";
            BotDebug("Macro '$function_name' completed, delay: $time_str", 'ACTION');
        } else {
            BotDebug("Macro '$function_name' completed successfully", 'ACTION');
        }

        // If the macro returns an int > 0, treat as delay (seconds) for handler
        if (is_int($result) && $result > 0) {
            return $result;
        }
        return true;
    } catch (Exception $e) {
        BotDebug("Exception in macro '$function_name': " . $e->getMessage(), 'ERROR');
        return false;
    }
}

/**
 * Execute a bot condition check
 *
 * @param string $function_name The condition function to execute
 * @return bool The result of the condition check
 */
function ExecuteBotCondition($function_name)
{
    global $BotID;

    if (!function_exists($function_name)) {
        BotDebug("Condition function '$function_name' not found", 'ERROR');
        return false;
    }

    try {
        // Check if function requires botID parameter
        $reflection = new ReflectionFunction($function_name);
        $param_count = $reflection->getNumberOfParameters();

        if ($param_count > 0) {
            // Function requires parameters (e.g., botID)
            $result = call_user_func($function_name, $BotID);
        } else {
            // Function requires no parameters
            $result = call_user_func($function_name);
        }

        $result_str = $result ? 'TRUE' : 'FALSE';
        BotDebug("Condition '$function_name' evaluated to $result_str", 'CONDITION');

        return (bool) $result;
    } catch (Exception $e) {
        BotDebug("Exception in condition '$function_name': " . $e->getMessage(), 'ERROR');
        return false;
    }
}

/**
 * Add a bot block execution to the queue
 *
 * @param int $player_id The bot's player ID
 * @param int $strat_id The strategy ID
 * @param int $block_id The block ID to execute
 * @param int $when The start time (timestamp)
 * @param int $seconds Delay in seconds before execution
 * @return int The queue task ID
 */
function AddBotQueue($player_id, $strat_id, $block_id, $when, $seconds)
{
    $start = max($when, time());
    $queue = ['', $player_id, 'AI', $strat_id, $block_id, 0, $start, $start + $seconds, 1000];
    return AddDBRow($queue, 'queue');
}

/**
 * Find a label block in a strategy by its text
 *
 * @param array $strat The strategy data array
 * @param string $labelText The label text to search for
 * @return int|null The label's key ID or null if not found
 */
function findLabelInStrategy($strat, $labelText)
{
    if (!isset($strat['nodeDataArray']) || !is_array($strat['nodeDataArray'])) {
        return null;
    }

    foreach ($strat['nodeDataArray'] as $arr) {
        if (isset($arr['text'], $arr['category'], $arr['key'])
            && $arr['text'] === $labelText
            && $arr['category'] === 'Label') {
            return $arr['key'];
        }
    }
    return null;
}

/**
 * Handle condition evaluation and determine which branch to take
 *
 * @param bool $result The condition evaluation result
 * @param array $childs Array of child branches
 * @param string $block_text The condition text for logging
 * @return int The next block ID or 0xdeadbeef if no valid branch found
 */
function handleConditionBranching($result, $childs, $block_text)
{
    $no_branch_found = 0xdeadbeef;
    $block_no = $no_branch_found;
    $prefix = '';

    foreach ($childs as $child) {
        if (!isset($child['text']) || !isset($child['to'])) {
            continue;
        }

        $child_text_lower = strtolower($child['text']);

        // Handle "no" branch
        if ($child_text_lower === 'no') {
            if (!$result) {
                if (IsBotDebugEnabled()) {
                    BotDebug("Condition '$block_text' => {$prefix}NO", 'CONDITION');
                }
                return $child['to'];
            }
            $block_no = $child['to'];
            continue;
        }

        // Handle "yes" branch
        if ($child_text_lower === 'yes' && $result) {
            if (IsBotDebugEnabled()) {
                BotDebug("Condition '$block_text' => YES", 'CONDITION');
            }
            return $child['to'];
        }

        // Handle probability branch (e.g., "50%")
        if (preg_match('/^([0-9]{1,2}|100)%$/', $child['text'], $matches) && $result) {
            $prc = (int) str_replace('%', '', $matches[0]);
            $roll = mt_rand(1, 100);

            if ($roll <= $prc) {
                if (IsBotDebugEnabled()) {
                    BotDebug("Condition '$block_text' => PROBABLY($roll/$prc) YES", 'CONDITION');
                }
                return $child['to'];
            } else {
                if ($block_no == $no_branch_found) {
                    $prefix = "PROBABLY($roll/$prc) ";
                    $result = false;
                } else {
                    if (IsBotDebugEnabled()) {
                        BotDebug("Condition '$block_text' => PROBABLY($roll/$prc) NO", 'CONDITION');
                    }
                    return $block_no;
                }
            }
        }
    }

    return $block_no;
}

/**
 * Execute a condition check using either a predefined keyword or eval
 *
 * @param string $block_text The condition text to evaluate
 * @return bool The result of the condition check
 */
function executeConditionCheck($block_text)
{
    $result = false;
    $condition_executed = false;
    $condition_map = getConditionMap();
    $lower_text = strtolower(trim($block_text));

    // First, check for level condition syntax (e.g., "shipyard:2")
    if (strpos($lower_text, ':') !== false) {
        // Check for personality condition first
        if (strpos($lower_text, 'personality:') === 0) {
            $personalityResult = checkPersonalityCondition($lower_text);
            if ($personalityResult !== null) {
                $result_str = $personalityResult ? 'TRUE' : 'FALSE';
                BotDebug("Personality condition '$block_text' evaluated to $result_str", 'CONDITION');
                return $personalityResult;
            }
        }

        // Then check for level condition
        $levelResult = checkLevelCondition($lower_text);
        if ($levelResult !== null) {
            $result_str = $levelResult ? 'TRUE' : 'FALSE';
            BotDebug("Level condition '$block_text' evaluated to $result_str", 'CONDITION');
            return $levelResult;
        }
    }

    foreach ($condition_map as $keyword => $function_name) {
        if ($lower_text === $keyword || strpos($lower_text, $keyword) === 0) {
            $result = ExecuteBotCondition($function_name);
            $condition_executed = true;
            break;
        }
    }

    // If no condition keyword was found, fall back to eval
    if (!$condition_executed) {
        BotDebug("No condition keyword matched, using eval for: '$block_text'", 'CONDITION');

        try {
            $result = eval('return (' . $block_text . ');');
            $condition_executed = true;
            BotDebug("Eval condition '$block_text' returned " . ($result ? 'TRUE' : 'FALSE'), 'CONDITION');
        } catch (ParseError $e) {
            BotDebug("Parse error in condition '$block_text': " . $e->getMessage(), 'ERROR');
            $result = false;
        }
    }

    return (bool) $result;
}

/**
 * Evaluate and execute an action command (macro or eval)
 *
 * @param string $block_text The action text to execute
 * @return int Number of seconds to sleep/delay after execution
 */
function evaluateActionCommand($block_text)
{
    global $loca_lang;

    $sleep = 0;
    $macro_executed = false;

    // First, try to normalize localized block text to macro keyword
    $normalized_text = getMacroKeywordFromLocalizedText($block_text);
    if ($normalized_text !== null) {
        // This is a localized predefined block, use the keyword
        $block_text = $normalized_text;
        BotDebug("Localized block detected: '$normalized_text'", 'ACTION');
    }

    $macro_map = getMacroMap();
    $lower_text = strtolower(trim($block_text));

    foreach ($macro_map as $keyword => $function_name) {
        // Special handling for 'sleep' macro: allow 'sleep' or 'sleep:SECONDS'
        if ($keyword === 'sleep' && preg_match('/^sleep(:([0-9]+))?$/', $lower_text, $matches)) {
            $seconds = isset($matches[2]) ? (int) $matches[2] : 28800; // Default 8 hours
            $sleep = $seconds;
            $macro_executed = true;
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            $time_str = $hours > 0 ? "{$hours}h {$minutes}m" : "{$minutes}m";
            BotDebug("Sleep command: waiting for $time_str", 'ACTION');
            break;
        } elseif ($lower_text === $keyword || strpos($lower_text, $keyword) === 0) {
            // Pass the keyword to ExecuteBotMacro so buildMacro knows what to build
            $macro_result = ExecuteBotMacro($function_name, $keyword);
            if (is_int($macro_result) && $macro_result > 0) {
                $sleep = $macro_result;
            }
            $macro_executed = true;
            break;
        }
    }

    // If no macro was executed, fall back to eval
    if (!$macro_executed) {
        BotDebug("No macro keyword matched, using eval for: '$block_text'", 'ACTION');

        // Escape the block text for safe display in debug messages
        $block_text_quoted = htmlspecialchars($block_text, ENT_QUOTES, 'UTF-8');

        try {
            $sleep = @eval($block_text . ';');

            // Check if eval failed (returns false on parse error)
            if ($sleep === false) {
                BotDebug("Failed to evaluate action block: $block_text_quoted", 'ERROR');
                $sleep = 0;
            }
        } catch (Error $e) {
            BotDebug("Exception in action block '$block_text_quoted': " . $e->getMessage(), 'ERROR');
            $sleep = 0;
        } catch (ParseError $e) {
            BotDebug("Parse error in action block '$block_text_quoted': " . $e->getMessage(), 'ERROR');
            $sleep = 0;
        }
    }

    return $sleep ?? 0;
    foreach ($argsArr as &$arg) {
        if ($arg !== '' && !is_numeric($arg) && !preg_match('/^(["\']).*\1$/', $arg)) {
            $arg = '"' . str_replace('"', '\"', $arg) . '"';
        }
    }
    unset($arg);
    foreach ($argsArr as &$arg) {
        if (preg_match('/^(["\']).*\1$/', $arg)) {
            $arg = eval('return ' . $arg . ';');
        }
    }
    unset($arg);
}

/**
 * Main block interpreter - executes a bot strategy block
 *
 * @param array $queue The queue task data
 * @param array $block The block to execute
 * @param array $childs Array of child blocks/branches
 */
function ExecuteBlock($queue, $block, $childs)
{
    global $db_prefix, $BotID, $BotNow;

    $BotNow = $queue['end'];
    $BotID = $queue['owner_id'];
    $strat_id = $queue['sub_id'];
    $task_id = $queue['task_id'];

    // Update planet resources once at turn start
    $user = LoadUser($BotID);
    if ($user && isset($user['aktplanet'])) {
        $planet = GetPlanet($user['aktplanet']);
        if ($planet && $planet['type'] == 1) {
            $lastPeek = (int) $planet['lastpeek'];
            $effectiveNow = max($lastPeek, (int) $BotNow);
            if ($effectiveNow > $lastPeek) {
                ProdResources($planet, $lastPeek, $effectiveNow);
            }
        }
    }

    // Get block category, default to empty string for action blocks
    $block_category = $block['category'] ?? '';
    $block_key = $block['key'] ?? 'unknown';
    $block_text = $block['text'] ?? '';

    $category_display = !empty($block_category) ? $block_category : 'Action';
    BotDebug("Executing block: $category_display($block_key) - '$block_text'", 'INFO');

    switch ($block_category) {
        case 'Start':
            executeStartBlock($childs, $BotID, $strat_id, $BotNow, $task_id);
            break;

        case 'End':
            executeEndBlock($task_id);
            break;

        case 'Label':
            executeLabelBlock($childs, $BotID, $strat_id, $BotNow, $task_id);
            break;

        case 'Branch':
            executeBranchBlock($block_text, $BotID, $strat_id, $BotNow, $task_id, $db_prefix);
            break;

        case 'Cond':
            executeCondBlock($block_text, $childs, $BotID, $strat_id, $BotNow, $task_id);
            break;

        case '':
        default:
            processActionBlock($block_text, $childs, $BotID, $strat_id, $BotNow, $task_id);
            break;
    }
}

/**
 * Execute a Start block
 */
function executeStartBlock($childs, $BotID, $strat_id, $BotNow, $task_id)
{
    if (!empty($childs) && isset($childs[0]['to'])) {
        $block_id = $childs[0]['to'];
        AddBotQueue($BotID, $strat_id, $block_id, $BotNow, 0);
    }
    RemoveQueue($task_id);
}

/**
 * Execute an End block
 */
function executeEndBlock($task_id)
{
    RemoveQueue($task_id);
}

/**
 * Execute a Label block
 */
function executeLabelBlock($childs, $BotID, $strat_id, $BotNow, $task_id)
{
    // Select from all descendants the one that comes from the bottom of the block (fromPort="B")
    $block_id = isset($childs[0]['to']) ? $childs[0]['to'] : null;

    foreach ($childs as $child) {
        if (isset($child['fromPort']) && $child['fromPort'] === 'B' && isset($child['to'])) {
            $block_id = $child['to'];
            break;
        }
    }

    if ($block_id !== null) {
        AddBotQueue($BotID, $strat_id, $block_id, $BotNow, 0);
    }
    RemoveQueue($task_id);
}

/**
 * Execute a Branch block (goto label)
 */
function executeBranchBlock($block_text, $BotID, $strat_id, $BotNow, $task_id, $db_prefix)
{
    $query = 'SELECT * FROM ' . $db_prefix . "botstrat WHERE id = $strat_id LIMIT 1";
    $result = dbquery($query);

    if ($result && dbrows($result) > 0) {
        $row = dbarray($result);
        $strat = json_decode($row['source'], true);

        if (is_array($strat)) {
            $label_key = findLabelInStrategy($strat, $block_text);
            if ($label_key !== null) {
                BotDebug("Branch to label: '$block_text'", 'INFO');
                AddBotQueue($BotID, $strat_id, $label_key, $BotNow, 0);
            } else {
                BotDebug("Label not found: '$block_text'", 'ERROR');
            }
        } else {
            BotDebug('Invalid strategy format', 'ERROR');
        }
    } else {
        BotDebug('Failed to load strategy for branch', 'ERROR');
    }
    RemoveQueue($task_id);
}

/**
 * Execute a Condition block
 */
function executeCondBlock($block_text, $childs, $BotID, $strat_id, $BotNow, $task_id)
{
    $result = executeConditionCheck($block_text);
    $block_id = handleConditionBranching($result, $childs, $block_text);

    if ($block_id != 0xdeadbeef) {
        AddBotQueue($BotID, $strat_id, $block_id, $BotNow, 0);
    } else {
        BotDebug('Failed to select conditional branch', 'ERROR');
    }
    RemoveQueue($task_id);
}

/**
 * Process an Action block and schedule the next block
 */
function processActionBlock($block_text, $childs, $BotID, $strat_id, $BotNow, $task_id)
{
    // Action block - execute the block text
    if (!empty(trim($block_text))) {
        $sleep = evaluateActionCommand($block_text);
    } else {
        $sleep = 0;
        BotDebug('Action block has no text, skipping execution', 'INFO');
    }

    // Check if there's a child to continue to
    if (!empty($childs) && isset($childs[0]['to'])) {
        $block_id = $childs[0]['to'];
        AddBotQueue($BotID, $strat_id, $block_id, $BotNow, $sleep);
    } else {
        BotDebug('No child block found, ending execution', 'INFO');
    }
    RemoveQueue($task_id);
}
