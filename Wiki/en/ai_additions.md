### Adding Custom Personalities

1. Create new file in `game/bot/personalities/yourname.php`
2. Return array with structure matching `miner.php` or `fleeter.php`
3. Bot will automatically pick up new personality

### Adding Custom Macros

1. Add function to `macros.php`:
```php
function myCustomMacro($planet) {
    // Your logic here
    return $delay_seconds;
}
```

2. Register in macro map:
```php
function getMacroMap() {
    return [
        // ... existing macros
        'mycustom' => 'myCustomMacro'
    ];
}
```

### Adding Custom Conditions

1. Add function to `conditions.php`:
```php
function myCustomCondition($botID) {
    // Your logic here
    return true/false;
}
```

2. Register in condition map:
```php
function getConditionMap() {
    return [
        // ... existing conditions
        'mycondition' => 'myCustomCondition'
    ];
}
```

# Localized Predefined Bot Blocks - Implementation Guide

## Overview

The visual bot strategy designer now supports predefined, localized action blocks that appear in the user's language. These blocks provide commonly-used bot actions with translated labels while maintaining backward compatibility with existing strategies.

## Features

### 1. **Localized Block Labels**
Predefined blocks have been added in the user's language in the visual designer palette:

- **English**: "Build Mines", "Build Base", "Research", etc.
- **German**: "Minen bauen", "Basis bauen", "Forschen", etc.
- **Spanish**: "Construir Minas", "Construir Base", "Investigar", etc.
- **French**: "Construire des Mines", "Construire la Base", "Rechercher", etc.
- **Italian**: "Costruisci Miniere", "Costruisci Base", "Ricerca", etc.
- **Russian**: "Построить Шахты", "Построить Базу", "Исследовать", etc.

### 2. **Available Predefined Blocks**

| Loca Key | English Label | Macro Keyword | Function |
|----------|---------------|---------------|----------|
| BOT_BLOCK_MINES | Build Mines | `mines` | Build/upgrade metal, crystal, deuterium mines |
| BOT_BLOCK_BASIC | Build Base | `basic` | Build essential base structures |
| BOT_BLOCK_TECH | Build for Tech | `tech` | Build structures needed for tech unlocks |
| BOT_BLOCK_RESEARCH | Research | `research` | Research based on personality |
| BOT_BLOCK_BUILD | Build from Personality | `build` | Build based on personality ratios |
| BOT_BLOCK_SHIPYARD | Build Shipyard | `shipyard` | Build shipyard |
| BOT_BLOCK_ROBOTICS | Build Robotics Factory | `robotics` | Build robotics factory |
| BOT_BLOCK_RESLAB | Build Research Lab | `reslab` | Build research lab |
| BOT_BLOCK_NANITE | Build Nanite Factory | `nanite` | Build nanite factory |
| BOT_BLOCK_FUSION | Build Fusion Reactor | `fusion` | Build fusion reactor |
| BOT_BLOCK_TERRAFORMER | Build Terraformer | `terraformer` | Build terraformer |
| BOT_BLOCK_SILO | Build Missile Silo | `silo` | Build missile silo |
| BOT_BLOCK_SPEED | Build Speed Infrastructure | `speed` | Build robotics/nanites for faster builds |
| BOT_BLOCK_SLEEP | Sleep (8 hours) | `sleep` | Bot goes offline for 8 hours |

### Data Flow

```
User Interface (German user):
"Minen bauen" block in palette
       ↓
Visual Designer:
Saves as text: "Minen bauen"
       ↓
Strategy Storage (DB):
Stored in JSON: {"text": "Minen bauen", ...}
       ↓
Interpreter (evaluateActionCommand):
1. Reads: "Minen bauen"
2. Normalizes: "Minen bauen" → "mines"
3. Executes: buildMines() function
       ↓
Bot Action:
Builds mines on planet
```
## Adding New Predefined Blocks

To add a new predefined block type:

### 1. Add Localization Keys

Edit each language file in `game/loca/{lang}_{lang}/admin.php`:

```php
// English (en_en/admin.php)
$LOCA["en"]["BOT_BLOCK_YOURBLOCK"] = "Your Action Name";

// German (de_de/admin.php)
$LOCA["de"]["BOT_BLOCK_YOURBLOCK"] = "Deine Aktion";

// ... repeat for all languages
```

### 2. Add Mapping in localized_blocks.php

Edit `game/bot/localized_blocks.php`, add to `$blockDefinitions`:

```php
$blockDefinitions = [
    // ... existing entries ...
    'BOT_BLOCK_YOURBLOCK' => 'yourmacro',  // NEW
];
```

### 3. Add Macro Function

Edit `game/bot/macros.php`:

```php
// Add to getMacroMap()
function getMacroMap(): array {
    return [
        // ... existing entries ...
        'yourmacro' => 'yourMacroFunction',  // NEW
    ];
}

// Add your macro function
function yourMacroFunction(array $aktplanet): int {
    // Your implementation
    return 0; // Return delay in seconds
}
```

### 4. Add to JavaScript Palette

Edit `game/pages_admin/admin_botedit.php`:

```javascript
var botBlockLabels = {
  // ... existing entries ...
  yourmacro: "<?=loca('BOT_BLOCK_YOURBLOCK');?>"  // NEW
};
```

Edit `game/js/go-game.js`, add to palette blocks:

```javascript
if (typeof botBlockLabels !== 'undefined') {
  paletteBlocks.push(
    // ... existing entries ...
    { text: botBlockLabels.yourmacro }  // NEW
  );
}
```

## Backward Compatibility

- **Existing strategies**: Continue to work without changes
- **Keyword-based blocks**: Still supported (e.g., "mines", "basic")
- **Custom eval blocks**: Still supported for advanced users
- **Multi-language**: Strategies created in one language work when viewed in another (text displays as stored, but executes correctly)