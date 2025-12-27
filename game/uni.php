<?php

// Managing the parameters of the universe.

// Load the Universe.
function LoadUniverse()
{
    global $db_prefix;
    $query = 'SELECT * FROM ' . $db_prefix . 'uni;';
    $result = dbquery($query);
    return dbarray($result);
}

// Update News.
function UpdateNews($news1, $news2, $days)
{
    global $db_prefix, $db_connect;
    $until = time() + $days * 24 * 60 * 60;
    $safe_news1 = mysqli_real_escape_string($db_connect, $news1);
    $safe_news2 = mysqli_real_escape_string($db_connect, $news2);
    $query = 'UPDATE ' . $db_prefix . "uni SET news1 = '" . $safe_news1 . "', news2 = '" . $safe_news2 . "', news_until = $until";
    dbquery($query);
}

// Take out the news.
function DisableNews()
{
    global $db_prefix;
    $query = 'UPDATE ' . $db_prefix . 'uni SET news_until = 0';
    dbquery($query);
}

// Set the parameters of the universe (all at the same time)
function SetUniParam($speed, $fspeed, $acs, $fid, $did, $defrepair, $defrepair_delta, $galaxies, $systems, $rapid, $moons, $freeze, $lang, $battle_engine, $php_battle, $force_lang, $start_dm, $max_werf, $feedage)
{
    global $db_prefix;
    global $GlobalUni;

    $query = 'UPDATE ' . $db_prefix . "uni SET lang='" . $lang . "', battle_engine='" . $battle_engine . "', freeze=$freeze, speed=$speed, fspeed=$fspeed, acs=$acs, fid=$fid, did=$did, defrepair=$defrepair, defrepair_delta=$defrepair_delta, galaxies=$galaxies, systems=$systems, rapid=$rapid, moons=$moons, php_battle=$php_battle, force_lang=$force_lang, start_dm=$start_dm, max_werf=$max_werf, feedage=$feedage";
    dbquery($query);

    $GlobalUni = LoadUniverse();
}

// Set external links for the following menu items: Forum, Discord (new, since the forum format of communication is becoming less and less relevant), Tutorial, Rules, About Us.
// An empty string hides the menu item.
function SetExtLinks($ext_board, $ext_discord, $ext_tutorial, $ext_rules, $ext_impressum)
{
    global $db_prefix;
    global $GlobalUni;


    global $db_connect;
    $query = 'UPDATE ' . $db_prefix . 'uni SET ext_board=?, ext_discord=?, ext_tutorial=?, ext_rules=?, ext_impressum=?';
    $stmt = mysqli_prepare($db_connect, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'sssss', $ext_board, $ext_discord, $ext_tutorial, $ext_rules, $ext_impressum);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    $GlobalUni = LoadUniverse();
}

// Set the maximum number of users (administrators and operators do not count)
function SetMaxUsers($maxusers)
{
    global $db_prefix;
    global $GlobalUni;

    if ($maxusers > 0) {
        $query = 'UPDATE ' . $db_prefix . "uni SET maxusers=$maxusers";
        dbquery($query);

        $GlobalUni = LoadUniverse();
    }
}

// Reset game hack attempts counter (called during relogin)
function ResetHackCounter()
{
    global $db_prefix;
    $query = 'UPDATE ' . $db_prefix . 'uni SET hacks = 0';
    dbquery($query);
}

// Increment the game's hack attempt counter.
function IncrementHackCounter()
{
    global $db_prefix;
    $query = 'UPDATE ' . $db_prefix . 'uni SET hacks = hacks + 1';
    dbquery($query);
}