<?php

function mainmenu($select)
{
    $menu = [
        'home'    => ['href' => 'home.php',    'label' => loca('MENU_START')],
        'about'   => ['href' => 'about.php',   'label' => loca('MENU_ABOUT')],
        'preview' => ['href' => 'screenshots.php', 'label' => loca('MENU_PICTURES')],
        'reg'     => ['href' => 'register.php', 'label' => loca('MENU_REG')],
    ];

    foreach ($menu as $key => $item) {
        if ($select == $key) {
            echo '    <div class="menupoint">' . $item['label'] . "</div>\n";
        } else {
            echo '    <a href="' . $item['href'] . '">' . $item['label'] . "</a>\n";
        }
    }

    // Board menu item (if defined)
    $boardAddr = loca('BOARDADDR');
    if (!empty($boardAddr)) {
        $label = loca('MENU_BOARD');
        if ($select == 'board') {
            echo '    <div class="menupoint">' . $label . "</div>\n";
        } else {
            echo '    <a href="' . $boardAddr . '" target=_top>' . $label . "</a>\n";
        }
    }

    // Wiki menu item (if defined)
    $wikiAddr = loca('WIKIADDR');
    if (!empty($wikiAddr)) {
        $label = loca('MENU_WIKI');
        if ($select == 'wiki') {
            echo '    <div class="menupoint">' . $label . "</div>\n";
        } else {
            echo '    <a href="' . $wikiAddr . '" target=_top>' . $label . "</a>\n";
        }
    }
}
