<?php

// Technologies.

loca_add('menu', $GlobalUser['lang']);
loca_add('techtree', $GlobalUser['lang']);

if (key_exists('cp', $_GET)) {
    SelectPlanet($GlobalUser['player_id'], intval($_GET['cp']));
}
$GlobalUser['aktplanet'] = GetSelectedPlanet($GlobalUser['player_id']);

$now = time();
UpdateQueue($now);
$aktplanet = GetPlanet($GlobalUser['aktplanet']);
ProdResources($aktplanet, $aktplanet['lastpeek'], $now);
UpdatePlanetActivity($aktplanet['planet_id']);
UpdateLastClick($GlobalUser['player_id']);

PageHeader('techtree');

BeginContent();

// **************************************************************************************
// A list of what-what-it-requires objects by category.

$req_building = [
    'name' => loca('TECHTREE_BUILDINGS'),
    1 => [],
    2 => [],
    3 => [],
    4 => [],
    12 => [3 => 5, 113 => 3],
    14 => [],
    15 => [14 => 10, 108 => 10],
    21 => [14 => 2],
    22 => [],
    23 => [],
    24 => [],
    31 => [],
    33 => [15 => 1, 113 => 12],
    34 => [],
    44 => [21 => 1],
];

$req_research = [
    'name' => loca('TECHTREE_RESEARCH'),
    106 => [31 => 3],
    108 => [31 => 1],
    109 => [31 => 4],
    110 => [113 => 3, 31 => 6],
    111 => [31 => 2],
    113 => [31 => 1],
    114 => [113 => 5, 110 => 5, 31 => 7],
    115 => [113 => 1],
    117 => [113 => 1, 31 => 2],
    118 => [114 => 3],
    120 => [113 => 2],
    121 => [31 => 4, 120 => 5, 113 => 4],
    122 => [113 => 8, 120 => 10, 121 => 5],
    123 => [31 => 10, 108 => 8, 114 => 8],
    124 => [106 => 4, 117 => 3],
    199 => [31 => 12],
];

$req_fleet = [
    'name' => loca('TECHTREE_FLEET'),
    202 => [21 => 2, 115 => 2],
    203 => [21 => 4, 115 => 6],
    204 => [21 => 1, 115 => 1],
    205 => [21 => 3, 111 => 2, 117 => 2],
    206 => [21 => 5, 117 => 4, 121 => 2],
    207 => [21 => 7, 118 => 4],
    208 => [21 => 4, 117 => 3],
    209 => [21 => 4, 115 => 6, 110 => 2],
    210 => [21 => 3, 115 => 3, 106 => 2],
    211 => [117 => 6, 21 => 8, 122 => 5],
    212 => [21 => 1],
    213 => [21 => 9, 118 => 6, 114 => 5],
    214 => [21 => 12, 118 => 7, 114 => 6, 199 => 1],
    215 => [114 => 5, 120 => 12, 118 => 5, 21 => 8],
];

$req_defense = [
    'name' => loca('TECHTREE_DEFENSE'),
    401 => [21 => 1],
    402 => [113 => 1, 21 => 2, 120 => 3],
    403 => [113 => 3, 21 => 4, 120 => 6],
    404 => [21 => 6, 113 => 6, 109 => 3, 110 => 1],
    405 => [21 => 4, 121 => 4],
    406 => [21 => 8, 122 => 7],
    407 => [110 => 2, 21 => 1],
    408 => [110 => 6, 21 => 6],
    502 => [44 => 2, 21 => 1],
    503 => [44 => 4, 21 => 1, 117 => 1],
];

$req_special = [
    'name' => loca('TECHTREE_SPECIAL'),
    41 => [],
    42 => [41 => 1],
    43 => [41 => 1, 114 => 7],
];

$reqs = [ $req_building, $req_research, $req_fleet, $req_defense, $req_special ];

function MeetRequirement($user, $planet, $id, $level)
{
    if (isResearch($id)) {
        return $user['r' . $id] >= $level;
    } else {
        return $planet['b' . $id] >= $level;
    }
}

echo "<center> \n";
echo "<table width=470> \n";

foreach ($reqs as $i => $req) {
    foreach ($req as $c => $entry) {
        if ($c === 'name') {
            echo "<tr><td class=c>$entry</td><td class=c>" . loca('TECHTREE_REQUIRED') . "</td></tr> \n";
        } else {
            if (count($entry) == 0) {
                $details = '&nbsp;';
            } else {
                $details = '<a href="index.php?page=techtreedetails&session=' . $_GET['session'] . "&tid=$c\">[i]</a>";
            }

            echo "<tr> \n";
            echo "<td class=l> \n";
            echo '<table width="100%" border=0 cellspacing=0 cellpadding=0><tr><td align=left><a href="index.php?page=infos&session=' . $_GET['session'] . "&gid=$c\">" . loca("NAME_$c") . "</a> \n";
            echo "</td><td align=right>$details</td></tr></table></td> \n";

            echo "<td class=l> \n";
            foreach ($entry as $obj => $lvl) {
                $ok = MeetRequirement($GlobalUser, $aktplanet, $obj, $lvl);
                if ($ok) {
                    echo '<font color="#00ff00">' . loca("NAME_$obj") . ' ' . va(loca('TECHTREE_LEVEL'), $lvl) . "</font><br /> \n";
                } else {
                    echo '<font color="#ff0000">' . loca("NAME_$obj") . ' ' . va(loca('TECHTREE_LEVEL'), $lvl) . "</font><br /> \n";
                }
            }
            echo "</td> \n";
        }
    }
    echo "\n";
}
echo "</table> \n";

echo "<br><br><br><br>\n";
EndContent();

PageFooter();
ob_end_flush();
