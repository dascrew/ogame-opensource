<?php

declare(strict_types=1);

/**
 * Buildings Page - Shipyard, Defense, and Research
 *
 * Handles the construction of ships, defense systems, and research technologies.
 * Processes both GET and POST requests for building queue management.
 */

// Constants
const MAX_SHIPYARD_QUEUE_SIZE = 99;
const MISSILE_CAPACITY_PER_SILO_LEVEL = 10;
const IPM_SILO_SIZE = 2;
const ABM_SILO_SIZE = 1;
const RESEARCH_TECHNOCRAT_BONUS = 1.1;
const RESEARCH_DEFAULT_SPEED = 1.0;

// Load localization resources
loca_add('menu', $GlobalUser['lang']);
loca_add('techshort', $GlobalUser['lang']);
loca_add('build', $GlobalUser['lang']);
loca_add('premium', $GlobalUser['lang']);

// Initialize page state
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
$session = $_GET['session'];

/**
 * Calculate maximum affordable units based on available resources
 */
function calculateMaxAffordable(array $planet, int $metal, int $crystal, int $deuterium): int
{
    $maxByMetal = $metal ? (int) floor($planet['m'] / $metal) : PHP_INT_MAX;
    $maxByCrystal = $crystal ? (int) floor($planet['k'] / $crystal) : PHP_INT_MAX;
    $maxByDeuterium = $deuterium ? (int) floor($planet['d'] / $deuterium) : PHP_INT_MAX;

    return min($maxByMetal, $maxByCrystal, $maxByDeuterium);
}

/**
 * Calculate free missile silo space
 */
function calculateFreeSiloSpace(array $planet): int
{
    $totalCapacity = $planet['b44'] * MISSILE_CAPACITY_PER_SILO_LEVEL;
    $usedSpace = ($planet['d502'] * ABM_SILO_SIZE) + ($planet['d503'] * IPM_SILO_SIZE);

    return $totalCapacity - $usedSpace;
}

/**
 * Process shipyard order submission
 */
function processShipyardOrder(array &$aktplanet, int $gid, int $value, array $GlobalUser, array $GlobalUni): int
{
    // Validate queue size
    $result = GetShipyardQueue($aktplanet['planet_id']);
    if (dbrows($result) >= MAX_SHIPYARD_QUEUE_SIZE) {
        return 0;
    }

    // Validate value
    if ($value <= 0) {
        return 0;
    }

    // Apply universe limit
    if ($value > $GlobalUni['max_werf']) {
        $value = $GlobalUni['max_werf'];
    }

    // Get unit cost
    $res = ShipyardPrice($gid);
    $m = $res['m'];
    $k = $res['k'];
    $d = $res['d'];

    // Check if player can afford at least one unit
    if ($aktplanet['m'] < $m || $aktplanet['k'] < $k || $aktplanet['d'] < $d) {
        return 0;
    }

    // Shield Domes are limited to 1
    if ($gid === GID_D_SDOME || $gid === GID_D_LDOME) {
        $value = 1;
    }

    // Limit missiles by silo capacity
    $freeSpace = calculateFreeSiloSpace($aktplanet);
    if ($gid === GID_D_ABM) {
        $value = min($freeSpace, $value);
    }
    if ($gid === GID_D_IPM) {
        $value = min((int) floor($freeSpace / IPM_SILO_SIZE), $value);
    }

    // Calculate maximum affordable amount
    $maxAffordable = calculateMaxAffordable($aktplanet, $m, $k, $d);
    if ($value > $maxAffordable) {
        $value = $maxAffordable;
    }

    return $value;
}

/**
 * Handle POST request for shipyard orders
 */
function handleShipyardPost(array &$aktplanet, array $GlobalUser, array $GlobalUni): void
{
    if (method() !== 'POST' || $GlobalUser['vacation']) {
        return;
    }

    foreach ($_POST['fmenge'] as $gid => $value) {
        $value = processShipyardOrder($aktplanet, (int) $gid, (int) $value, $GlobalUser, $GlobalUni);

        if ($value > 0) {
            AddShipyard($GlobalUser['player_id'], $aktplanet['planet_id'], (int) $gid, $value);
            $aktplanet = GetPlanet($aktplanet['planet_id']); // Refresh planet state
        }
    }
}

/**
 * Handle GET request for research operations
 */
function handleResearchGet(array &$aktplanet, array $GlobalUser, int $now): void
{
    if (method() !== 'GET' || $GlobalUser['vacation'] || $_GET['mode'] !== 'Forschung') {
        return;
    }

    $result = GetResearchQueue($GlobalUser['player_id']);
    $resqueue = dbarray($result);

    if (!$resqueue) {
        // No research in progress - start new research
        if (key_exists('bau', $_GET)) {
            StartResearch($GlobalUser['player_id'], $aktplanet['planet_id'], intval($_GET['bau']), $now);
            $aktplanet = GetPlanet($aktplanet['planet_id']); // Refresh planet state
        }
    } else {
        // Research in progress - cancel research
        if (key_exists('unbau', $_GET)) {
            StopResearch($GlobalUser['player_id']);
            $aktplanet = GetPlanet($aktplanet['planet_id']); // Refresh planet state
        }
    }
}

// Process requests
handleShipyardPost($aktplanet, $GlobalUser, $GlobalUni);
handleResearchGet($aktplanet, $GlobalUser, $now);

PageHeader('buildings');

BeginContent();

echo "<title> \n";
echo loca('BUILD_BUILDINGS_HEAD') . "\n";
echo "</title> \n";
echo "<script type=\"text/javascript\"> \n\n";
echo "function setMax(key, number){\n";
echo "    document.getElementsByName('fmenge['+key+']')[0].value=number;\n";
echo "}\n";
echo "</script> \n";

$unitab = LoadUniverse();
$speed = (int) $unitab['speed'];

/**
 * Render unit build form row
 */
function renderUnitRow(
    int $id,
    array $aktplanet,
    string $session,
    array $GlobalUser,
    int $speed,
    bool $busy,
    string $unitType = 'f' // 'f' for fleet, 'd' for defense
): void {
    $useSkin = (bool) $GlobalUser['useskin'];
    $premium = PremiumStatus($GlobalUser);

    echo '<tr>';

    if ($useSkin) {
        echo "<td class=l>\n";
        echo "<a href=index.php?page=infos&session=$session&gid=$id>\n";
        echo "<img border='0' src=\"" . UserSkin() . "gebaeude/$id.gif\" align='top' width='120' height='120'>\n";
        echo "</a>\n";
        echo "</td>\n";
        echo '<td class=l>';
    } else {
        echo '<td class=l colspan=2>';
    }

    echo "<a href=index.php?page=infos&session=$session&gid=$id>" . loca("NAME_$id") . '</a>';

    $currentAmount = $aktplanet[$unitType . $id];
    if ($currentAmount) {
        echo ' (' . va(loca('BUILD_SHIPYARD_UNITS'), $currentAmount) . ')';
    }

    $res = ShipyardPrice($id);
    $m = $res['m'];
    $k = $res['k'];
    $d = $res['d'];
    $e = $res['e'];

    echo '<br>' . loca("SHORT_$id") . '<br>' . loca('BUILD_PRICE') . ':';
    if ($m) {
        echo ' ' . loca('METAL') . ': <b>' . nicenum($m) . '</b>';
    }
    if ($k) {
        echo ' ' . loca('CRYSTAL') . ': <b>' . nicenum($k) . '</b>';
    }
    if ($d) {
        echo ' ' . loca('DEUTERIUM') . ': <b>' . nicenum($d) . '</b>';
    }
    if ($e) {
        echo ' ' . loca('ENERGY') . ': <b>' . nicenum($e) . '</b>';
    }

    $t = ShipyardDuration($id, $aktplanet['b21'], $aktplanet['b15'], $speed);
    echo '<br>' . loca('BUILD_DURATION') . ': ' . BuildDurationFormat($t) . '<br></th>';
    echo '<td class=k>';

    if (!checkRequirements($id, $GlobalUser, $aktplanet)['met']) {
        echo '<font color=#FF0000>' . loca('BUILD_SHIPYARD_CANT') . '</font>';
    } elseif (IsEnoughResources($aktplanet, $m, $k, $d, $e) && !$busy) {
        echo "<input type=text name='fmenge[$id]' alt='" . loca("NAME_$id") . "' size=6 maxlength=6 value=0 tabindex=1> ";

        if ($premium['commander']) {
            $max = calculateMaxAffordable($aktplanet, $m, $k, $d);

            // Special handling for domes (max 1)
            if ($id === GID_D_SDOME || $id === GID_D_LDOME) {
                $max = 1;
            }

            // Special handling for missiles
            if ($id === GID_D_ABM || $id === GID_D_IPM) {
                $freeSpace = calculateFreeSiloSpace($aktplanet);
                if ($id === GID_D_ABM) {
                    $max = min($max, $freeSpace);
                } else {
                    $max = min($max, (int) floor($freeSpace / IPM_SILO_SIZE));
                }
            }

            // Apply universe limit
            global $GlobalUni;
            $max = min($max, $GlobalUni['max_werf']);

            echo "<br><a href=\"javascript:setMax($id, $max);\">(max. $max)</a>";
        }
    }

    echo '</td></tr>';
}

/**
 * Render shipyard/defense build section
 */
function renderShipyardSection(
    array $aktplanet,
    array $GlobalUser,
    string $session,
    string $mode,
    int $speed,
    array $unitMap
): void {
    $busy = isShipyardBusy($aktplanet);
    $unitType = ($mode === 'Flotte') ? 'f' : 'd';

    if ($busy) {
        echo '<br><br><font color=#FF0000>' . loca('BUILD_ERROR_SHIPYARD_BUSY') . '</font><br><br>';
    }
    if ($GlobalUser['vacation']) {
        echo '<font color=#FF0000><center>' . va(loca('BUILD_ERROR_VACATION'), date('Y-m-d H:i:s', $GlobalUser['vacation_until'])) . '</center></font>';
    }

    echo "<form action=index.php?page=buildings&session=$session&mode=$mode method=post>";
    echo "<table align=top><tr><td style='background-color:transparent;'>";

    if ($GlobalUser['useskin']) {
        echo "<table width=\"530\">\n";
    } else {
        echo "<table width=\"468\">\n";
    }

    echo "<tr>\n";
    echo '<td class=l colspan="2">' . loca('BUILD_DESC') . "</td>\n";
    echo '<td class=l><b>' . loca('BUILD_AMOUNT') . "</b></td>\n";
    echo "</tr>\n\n";

    if ($aktplanet['b21']) {
        foreach ($unitMap as $id) {
            // Skip units that don't meet requirements and have zero count
            if (!checkRequirements($id, $GlobalUser, $aktplanet)['met']) {
                if ($aktplanet[$unitType . $id] <= 0) {
                    continue;
                }
            }

            // Special check for domes
            if ($unitType === 'd' && ($id === GID_D_SDOME || $id === GID_D_LDOME)) {
                if ($aktplanet[$unitType . $id] > 0 && !$busy) {
                    // Dome already exists, show warning instead of input
                    renderUnitRowWithDomeWarning($id, $aktplanet, $session, $GlobalUser, $speed);
                    continue;
                }
            }

            renderUnitRow($id, $aktplanet, $session, $GlobalUser, $speed, $busy, $unitType);
        }

        echo '<td class=c colspan=2 align=center><input type=submit value="' . loca('BUILD_SHIPYARD_SUBMIT') . '"></td></tr>';
    } else {
        if (!$busy) {
            echo '<table><tr><td class=c>' . loca('BUILD_ERROR_SHIPYARD_REQUIRED') . '</td></tr></table>';
        }
    }

    echo '</table>';
    echo '</form>';
    echo "</table>\n";
}

/**
 * Check if shipyard or nanite factory is under construction
 */
function isShipyardBusy(array $planet): bool
{
    $result = GetBuildQueue($planet['planet_id']);
    $queue = dbarray($result);

    return $queue && ($queue['tech_id'] == GID_B_SHIPYARD || $queue['tech_id'] == GID_B_NANITES);
}

/**
 * Check if research lab is being upgraded on any planet
 */
function isResearchLabBusy(int $playerId, int $now): bool
{
    global $db_prefix;

    $query = "SELECT * FROM {$db_prefix}queue WHERE obj_id = " . GID_B_RES_LAB .
             " AND (type = 'Build' OR type = 'Demolish') AND start < $now AND owner_id = $playerId";
    $result = dbquery($query);

    return dbrows($result) > 0;
}

/**
 * Render research timer countdown JavaScript
 */
function renderResearchTimer(array $researchQueue, array $aktplanet, string $session): void
{
    $endTime = $researchQueue['end'] - time();
    $sessionVar = htmlspecialchars($session);
    $planetId = $aktplanet['planet_id'];
    $objId = $researchQueue['obj_id'];
    $subId = $researchQueue['sub_id'];
    $completeText = loca('BUILD_COMPLETE');
    $nextText = loca('BUILD_RESEARCH_NEXT');
    $cancelText = loca('BUILD_CANCEL');
    $showCancel = ($aktplanet['planet_id'] == $researchQueue['sub_id']) ? 'true' : 'false';

    echo <<<HTML
<div id="bxx" class="z"></div>
<script type="text/javascript">
v=new Date();
var bxx=document.getElementById('bxx');
function t(){
    n=new Date();
    ss=$endTime;
    s=ss-Math.round((n.getTime()-v.getTime())/1000.);
    m=0;h=0;
    if(s<0){
        bxx.innerHTML='$completeText<br><a href=index.php?page=buildings&session=$sessionVar&mode=Forschung&cp=$planetId>$nextText</a>';
    }else{
        if(s>59){
            m=Math.floor(s/60);
            s=s-m*60
        }
        if(m>59){
            h=Math.floor(m/60);
            m=m-h*60
        }
        if(s<10){
            s="0"+s
        }
        if(m<10){
            m="0"+m
        }
        var cancelLink = $showCancel ? '">$cancelText</a>"' : '';
        bxx.innerHTML=h+":"+m+":"+s+"<br><a href=index.php?page=buildings&session=$sessionVar&unbau=$objId&mode=Forschung&cp=$subId"+cancelLink;
    }
    window.setTimeout("t();",999);
}
window.onload=t;
</script>
HTML;
}

/**
 * Render single research row
 */
function renderResearchRow(
    int $id,
    array $aktplanet,
    array $GlobalUser,
    string $session,
    int $speed,
    float $researchFactor,
    bool $operating,
    ?array $researchQueue
): void {
    $useSkin = (bool) $GlobalUser['useskin'];
    $premium = PremiumStatus($GlobalUser);
    $reslab = ResearchNetwork($aktplanet['planet_id'], $id);
    $level = $GlobalUser['r' . $id] + 1;

    echo '<tr>';

    if ($useSkin) {
        echo "<td class=l>\n";
        echo "<a href=index.php?page=infos&session=$session&gid=$id>\n";
        echo "<img border='0' src=\"" . UserSkin() . "gebaeude/$id.gif\" align='top' width='120' height='120'>\n";
        echo "</a>\n";
        echo "</td>\n";
        echo '<td class=l>';
    } else {
        echo '<td class=l colspan=2>';
    }

    echo "<a href=index.php?page=infos&session=$session&gid=$id>" . loca("NAME_$id") . '</a>';

    if ($GlobalUser['r' . $id]) {
        echo ' (' . va(loca('BUILD_LEVEL'), $GlobalUser['r' . $id]);

        if ($id == GID_R_ESPIONAGE && $premium['technocrat']) {
            echo ' <b><font style="color:lime;">+2</font></b> ';
            echo '<img border="0" src="img/technokrat_ikon.gif" alt="' . loca('PREM_TECHNOCRATE') . '" ';
            echo "onmouseover=\"return overlib('<font color=white>" . loca('PREM_TECHNOCRATE') . "</font>', WIDTH, 100);\" ";
            echo "onmouseout='return nd();' width=\"20\" height=\"20\" style=\"vertical-align:middle;\">";
        }

        echo ')';
    }

    $res = ResearchPrice($id, $level);
    $m = $res['m'];
    $k = $res['k'];
    $d = $res['d'];
    $e = $res['e'];

    echo '<br>' . loca("SHORT_$id") . '<br>' . loca('BUILD_PRICE') . ':';
    if ($m) {
        echo ' ' . loca('METAL') . ': <b>' . nicenum($m) . '</b>';
    }
    if ($k) {
        echo ' ' . loca('CRYSTAL') . ': <b>' . nicenum($k) . '</b>';
    }
    if ($d) {
        echo ' ' . loca('DEUTERIUM') . ': <b>' . nicenum($d) . '</b>';
    }
    if ($e) {
        echo ' ' . loca('ENERGY') . ': <b>' . nicenum($e) . '</b>';
    }

    $t = ResearchDuration($id, $level, $reslab, $speed * $researchFactor);
    echo '<br>' . loca('BUILD_DURATION') . ': ' . BuildDurationFormat($t) . '<br></th>';
    echo '<td class=k>';

    if ($operating) {
        if ($id == $researchQueue['obj_id']) {
            renderResearchTimer($researchQueue, $aktplanet, $session);
        } else {
            echo ' - ';
        }
    } else {
        if ($GlobalUser['r' . $id]) {
            if (IsEnoughResources($aktplanet, $m, $k, $d, $e)) {
                echo " <a href=index.php?page=buildings&session=$session&mode=Forschung&bau=$id>";
                echo '<font color=#00FF00>' . va(loca('BUILD_RESEARCH_LEVEL'), $level) . '</font></a>';
            } else {
                echo '<font color=#FF0000>' . va(loca('BUILD_RESEARCH_LEVEL'), $level) . '</font>';
            }
        } else {
            if (IsEnoughResources($aktplanet, $m, $k, $d, $e)) {
                echo " <a href=index.php?page=buildings&session=$session&mode=Forschung&bau=$id>";
                echo '<font color=#00FF00>' . loca('BUILD_RESEARCH') . '</font></a>';
            } else {
                echo '<font color=#FF0000>' . loca('BUILD_RESEARCH') . '</font></a>';
            }
        }
    }

    echo '</td></tr>';
}

/**
 * Render research section
 */
function renderResearchSection(
    array $aktplanet,
    array $GlobalUser,
    string $session,
    int $speed,
    int $now,
    array $resMap
): void {
    $premium = PremiumStatus($GlobalUser);
    $researchFactor = $premium['technocrat'] ? RESEARCH_TECHNOCRAT_BONUS : RESEARCH_DEFAULT_SPEED;

    $busy = isResearchLabBusy((int) $GlobalUser['player_id'], $now);

    $res = GetResearchQueue($GlobalUser['player_id']);
    $researchQueue = dbarray($res);
    if ($researchQueue === false) {
        $researchQueue = null;
    }
    $operating = ($researchQueue !== null);

    if ($busy) {
        echo '<br><br><font color=#FF0000>' . loca('BUILD_ERROR_RESLAB_BUSY') . '</font><br /><br />';
    }
    if ($GlobalUser['vacation']) {
        echo '<font color=#FF0000><center>' . va(loca('BUILD_ERROR_VACATION'), date('Y-m-d H:i:s', $GlobalUser['vacation_until'])) . '</center></font>';
    }

    echo "<table align=top><tr><td style='background-color:transparent;'>";

    if ($GlobalUser['useskin']) {
        echo "<table width=\"530\">\n";
    } else {
        echo "<table width=\"468\">\n";
    }

    echo "<tr>\n";
    echo '<td class=l colspan="2">' . loca('BUILD_DESC') . "</td>\n";
    echo '<td class=l><b>' . loca('BUILD_AMOUNT') . "</b></td>\n";
    echo "</tr>\n\n";

    if ($aktplanet['b31']) {
        foreach ($resMap as $id) {
            if (!checkRequirements($id, $GlobalUser, $aktplanet)['met']) {
                continue;
            }

            renderResearchRow($id, $aktplanet, $GlobalUser, $session, $speed, $researchFactor, $operating, $researchQueue);
        }
    } else {
        if (!$busy) {
            echo '<table><tr><td class=c>' . loca('BUILD_ERROR_RESLAB_REQUIRED') . '</td></tr></table>';
        }
    }

    echo '</table>';
    echo "</table>\n";
}

/**
 * Render dome warning row
 */
function renderUnitRowWithDomeWarning(int $id, array $aktplanet, string $session, array $GlobalUser, int $speed): void
{
    $useSkin = (bool) $GlobalUser['useskin'];

    echo '<tr>';

    if ($useSkin) {
        echo "<td class=l>\n";
        echo "<a href=index.php?page=infos&session=$session&gid=$id>\n";
        echo "<img border='0' src=\"" . UserSkin() . "gebaeude/$id.gif\" align='top' width='120' height='120'>\n";
        echo "</a>\n";
        echo "</td>\n";
        echo '<td class=l>';
    } else {
        echo '<td class=l colspan=2>';
    }

    echo "<a href=index.php?page=infos&session=$session&gid=$id>" . loca("NAME_$id") . '</a>';
    echo ' (' . va(loca('BUILD_SHIPYARD_UNITS'), $aktplanet['d' . $id]) . ')';

    $res = ShipyardPrice($id);
    $m = $res['m'];
    $k = $res['k'];
    $d = $res['d'];

    echo '<br>' . loca("SHORT_$id") . '<br>' . loca('BUILD_PRICE') . ':';
    if ($m) {
        echo ' ' . loca('METAL') . ': <b>' . nicenum($m) . '</b>';
    }
    if ($k) {
        echo ' ' . loca('CRYSTAL') . ': <b>' . nicenum($k) . '</b>';
    }
    if ($d) {
        echo ' ' . loca('DEUTERIUM') . ': <b>' . nicenum($d) . '</b>';
    }

    $t = ShipyardDuration($id, $aktplanet['b21'], $aktplanet['b15'], $speed);
    echo '<br>' . loca('BUILD_DURATION') . ': ' . BuildDurationFormat($t) . '<br></th>';
    echo '<td class=k>';
    echo '<font color=#FF0000>' . loca('BUILD_ERROR_DOME') . '</font>';
    echo '</td></tr>';
}

// ************************************************ Shipyard ************************************************

if ($_GET['mode'] === 'Flotte') {
    renderShipyardSection($aktplanet, $GlobalUser, $session, 'Flotte', $speed, $fleetmap);
}

// ************************************************ Defense ************************************************

if ($_GET['mode'] === 'Verteidigung') {
    renderShipyardSection($aktplanet, $GlobalUser, $session, 'Verteidigung', $speed, $defmap);
}

// ************************************************ Research ************************************************

if ($_GET['mode'] === 'Forschung') {
    renderResearchSection($aktplanet, $GlobalUser, $session, $speed, $now, $resmap);
}

// ***********************************************************************
/**
 * Render shipyard queue JavaScript and UI
 */
function renderShipyardQueue(array $aktplanet, string $session, int $now): void
{
    $result = GetShipyardQueue($aktplanet['planet_id']);
    $rows = dbrows($result);

    if (!$rows) {
        return;
    }

    $first = true;
    $durations = '';
    $names = '';
    $amounts = '';
    $totalTime = 0;
    $startOffset = 0;

    while ($rows--) {
        $queue = dbarray($result);
        if ($first) {
            $startOffset = $now - $queue['start'];
            $first = false;
        }
        $duration = $queue['end'] - $queue['start'];
        $durations .= $duration . ',';
        $names .= '"' . loca('NAME_' . $queue['obj_id']) . '",';
        $amounts .= '"' . $queue['level'] . '",';
        $totalTime += $duration * $queue['level'];
    }
    $totalTime -= $startOffset;

    $completeText = loca('BUILD_SHIPYARD_COMPLETE');
    $currentText = loca('BUILD_SHIPYARD_CURRENT');
    $queueText = loca('BUILD_SHIPYARD_QUEUE');
    $timeText = loca('BUILD_SHIPYARD_TIME');
    $sessionEscaped = htmlspecialchars($session);

    echo <<<HTML
<br>Сейчас производится: <div id="bx" class="z"></div>

<script type="text/javascript">
v = new Date();
p = 0;
g = $startOffset;
s = 0;
hs = 0;
of = 1;
c = new Array($durations"");
b = new Array($names"");
a = new Array($amounts"");
aa = "$completeText";

function t() {
    if (hs == 0) {
        xd();
        hs = 1;
    }
    n = new Date();
    s = c[p]-g-Math.round((n.getTime()-v.getTime())/1000.);
    s = Math.round(s);
    m = 0;
    h = 0;
    if (s < 0) {
        a[p]--;
        xd();
        if (a[p] <= 0) {
            p++;
            xd();
        }
        g = 0;
        v = new Date();
        s=0;
    }
    if (s > 59) {
        m = Math.floor(s / 60);
        s = s - m * 60;
    }
    if (m > 59) {
        h = Math.floor(m / 60);
        m = m - h * 60;
    }
    if (s < 10) {
        s = "0" + s;
    }
    if (m < 10) {
        m = "0" + m;
    }
    if (p > b.length - 2) {
        document.getElementById("bx").innerHTML=aa;
    } else {
        document.getElementById("bx").innerHTML=b[p]+" "+h+":"+m+":"+s;
    }
    window.setTimeout("t();", 200);
}

function xd() {
    while (document.Atr.auftr.length > 0) {
        document.Atr.auftr.options[document.Atr.auftr.length-1] = null;
    }
    if (p > b.length - 2) {
        document.Atr.auftr.options[document.Atr.auftr.length] = new Option(aa);
    }
    for (iv = p; iv <= b.length - 2; iv++) {
        ae = " ";
        if (iv == p) {
            act = "$currentText";
        } else {
            act = "";
        }
        document.Atr.auftr.options[document.Atr.auftr.length] = new Option(a[iv]+ae+" \\""+b[iv]+"\\""+act, iv + of);
    }
}

window.onload = t;
document.addEventListener("visibilitychange", function() {
    if (!document.hidden) {
        t();
    }
});
</script>

<br>
<form name="Atr" method="get" action="index.php?page=buildings">
<input type="hidden" name="session" value="$sessionEscaped">
<input type="hidden" name="mode" value="Flotte">
<table width="530">
 <tr>
    <td class="c">$queueText</td>
 </tr>
 <tr>
  <th><select name="auftr" size="10"></select></th>
 </tr>
 <tr>
  <td class="c"></td>
 </tr>
</table>
</form>
$timeText

HTML;

    echo BuildDurationFormat($totalTime) . "<br>\n";
}

echo '</table>';
if ($_GET['mode'] === 'Verteidigung' || $_GET['mode'] === 'Flotte') {
    echo '</form>';
}
echo "</table>\n";

if ($_GET['mode'] === 'Verteidigung' || $_GET['mode'] === 'Flotte') {
    renderShipyardQueue($aktplanet, $session, $now);
}

echo "<br><br><br><br>\n";
EndContent();

PageFooter();
ob_end_flush();
