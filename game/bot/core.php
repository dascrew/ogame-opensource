<?php

// Bot Core Functions - Bot lifecycle management and variable storage

require_once  'api.php';
require_once  'interpreter.php';
/**
 * Add a new bot player
 *
 * @param string $name Bot username
 * @return bool Success status
 */
function AddBot($name)
{
    global $db_prefix;

    // Generate password
    $len = 8;
    $r = '';
    for ($i = 0; $i < $len; $i++) {
        $r .= chr(rand(0, 25) + ord('a'));
    }
    $pass = $r;

    if (!IsUserExist($name)) {
        $player_id = CreateUser($name, $pass, '', true);
        $query = 'UPDATE ' . $db_prefix . "users SET validatemd = '', validated = 1 WHERE player_id = " . $player_id;
        dbquery($query);
        StartBot($player_id);
        SetVar($player_id, 'password', $pass);

        $personalities = glob(__DIR__ . '/../personalities/*.php');
        if (!empty($personalities)) {
            $randomPersonality = basename($personalities[array_rand($personalities)], '.php');
            BotSetVar($player_id, 'personality', $randomPersonality);
        }

        return true;
    } else {
        return false;
    }
}

/**
 * Start a bot (execute the Start block for the _start strategy)
 *
 * @param int $player_id Bot player ID
 */
function StartBot($player_id)
{
    global $BotID, $BotNow;

    $BotID = $player_id;
    $BotNow = time();

    if (BotExec('_start') == 0) {
        Debug('Bot error: Start strategy not found.');
    }
}

/**
 * Stop a bot (remove all AI tasks)
 *
 * @param int $player_id Bot player ID
 */
function StopBot($player_id)
{
    global $db_prefix;

    if (IsBot($player_id)) {
        $query = 'DELETE FROM ' . $db_prefix . "queue WHERE type = 'AI' AND owner_id = $player_id";
        dbquery($query);
    }
}

/**
 * Check if a player is a bot
 *
 * @param int $player_id Player ID to check
 * @return bool True if player is a bot
 */
function IsBot($player_id)
{
    global $db_prefix;
    $query = 'SELECT * FROM ' . $db_prefix . "queue WHERE type = 'AI' AND owner_id = $player_id";
    $result = dbquery($query);
    return dbrows($result) > 0;
}

/**
 * Task completion event for the bot. Called from queue.php
 * Activate the bot's task parser.
 *
 * @param array $queue Queue task data
 */
function Queue_Bot_End($queue)
{
    global $db_prefix, $BotID, $BotNow;

    $query = 'SELECT * FROM ' . $db_prefix . 'botstrat WHERE id = ' . $queue['sub_id'] . ' LIMIT 1';
    $result = dbquery($query);

    if ($result && dbrows($result) > 0) {
        $row = dbarray($result);
        $strat = json_decode($row['source'], true);

        if (is_array($strat) && isset($strat['nodeDataArray'])) {
            foreach ($strat['nodeDataArray'] as $arr) {
                if ($arr['key'] == $queue['obj_id']) {
                    $block = $arr;

                    $childs = [];
                    foreach ($strat['linkDataArray'] as $arr) {
                        if ($arr['from'] == $block['key']) {
                            $childs[] = $arr;
                        }
                    }

                    ExecuteBlock($queue, $block, $childs);
                    break;
                }
            }
        }
    } else {
        Debug('Bot error: Failed to load strategy ' . $queue['sub_id']);
    }
}

/**
 * Get a bot variable value
 *
 * @param int $owner_id Bot owner ID
 * @param string $var Variable name
 * @param mixed $def_value Default value if not found
 * @return mixed Variable value or default
 */
function GetVar($owner_id, $var, $def_value = null)
{
    global $db_prefix;
    $query = 'SELECT * FROM ' . $db_prefix . "botvars WHERE var = '" . $var . "' AND owner_id = $owner_id LIMIT 1;";
    $result = dbquery($query);

    if (dbrows($result) > 0) {
        $var_data = dbarray($result);
        return $var_data['value'];
    } else {
        $var_data = ['', $owner_id, $var, $def_value];
        AddDBRow($var_data, 'botvars');
        return $def_value;
    }
}

/**
 * Set a bot variable value
 *
 * @param int $owner_id Bot owner ID
 * @param string $var Variable name
 * @param mixed $value Value to set
 */
function SetVar($owner_id, $var, $value)
{
    global $db_prefix;
    $query = 'SELECT * FROM ' . $db_prefix . "botvars WHERE var = '" . $var . "' AND owner_id = $owner_id LIMIT 1;";
    $result = dbquery($query);

    if (dbrows($result) > 0) {
        $query = 'UPDATE ' . $db_prefix . "botvars SET value = '" . $value . "' WHERE var = '" . $var . "' AND owner_id = $owner_id;";
        dbquery($query);
    } else {
        $var_data = ['', $owner_id, $var, $value];
        AddDBRow($var_data, 'botvars');
    }
}
