# Bot Commands Reference

This document provides a comprehensive reference for all commands that can be used in action blocks and condition blocks within the bot system's visual strategy designer.

## Table of Contents

- [Action Block Commands](#action-block-commands)
  - [Predefined Macros](#predefined-macros)
  - [Building Commands](#building-commands)
  - [Research Commands](#research-commands)
  - [Utility Commands](#utility-commands)
  - [Custom Eval Commands](#custom-eval-commands)
- [Condition Block Commands](#condition-block-commands)
  - [Predefined Conditions](#predefined-conditions)
  - [Level Conditions](#level-conditions)
  - [Personality Conditions](#personality-conditions)
  - [Custom Eval Conditions](#custom-eval-conditions)
- [Built-in API Functions](#built-in-api-functions)

---

## Action Block Commands

Action blocks execute commands that perform operations such as building structures, researching technologies, or controlling bot behavior.

### Predefined Macros

These are high-level macros that handle common bot tasks automatically. Simply type the keyword in an action block:

| Command | Description | Returns |
|---------|-------------|---------|
| `mines` | Builds or upgrades mines (Metal, Crystal, Deuterium) on the current planet. Waits if build queue is active. Builds Solar Plant if energy is insufficient. | Delay in seconds |
| `basic` | Builds essential base structures (Metal Mine level 4, Crystal Mine level 2, Deuterium Synthesizer level 1, Research Lab level 1). Prioritizes Solar Plant if energy is insufficient. | Delay in seconds |
| `tech` | Builds or researches prerequisites for advanced structures (Shipyard, Missile Silo, Fusion Reactor, Nanite Factory, Terraformer). | Delay in seconds |
| `research` | Researches technologies based on the bot's personality configuration and upgrade priorities. | Delay in seconds |
| `build` | Builds structures based on the bot's personality configuration and upgrade priorities. | Delay in seconds |
| `speed` | Optimizes build speed by upgrading Robotics Factory or Nanite Factory if they reduce build time. | Delay in seconds |
| `sleep` | Delays execution for a specified time (default: 8 hours / 28800 seconds). | Delay in seconds |
| `basicres` | Researches basic technologies (Energy Tech 3, Combustion Drive 3, Impulse Drive 3, Espionage Tech 2, Computer Tech 2). | Delay in seconds |

### Building Commands

Build specific structures by using their keyword:

| Command | Description | Returns |
|---------|-------------|---------|
| `shipyard` | Builds or upgrades the Shipyard. | Delay in seconds |
| `robotics` | Builds or upgrades the Robotics Factory. | Delay in seconds |
| `reslab` | Builds or upgrades the Research Lab. | Delay in seconds |
| `nanite` | Builds or upgrades the Nanite Factory. | Delay in seconds |
| `fusion` | Builds or upgrades the Fusion Reactor. | Delay in seconds |
| `terraformer` | Builds or upgrades the Terraformer. | Delay in seconds |
| `silo` | Builds or upgrades the Missile Silo. | Delay in seconds |
| `metal` | Builds or upgrades the Metal Mine. | Delay in seconds |
| `crystal` | Builds or upgrades the Crystal Mine. | Delay in seconds |
| `deuterium` | Builds or upgrades the Deuterium Synthesizer. | Delay in seconds |
| `metalstor` | Builds or upgrades Metal Storage. | Delay in seconds |
| `crystalstor` | Builds or upgrades Crystal Storage. | Delay in seconds |
| `deutstor` | Builds or upgrades Deuterium Storage. | Delay in seconds |
| `solar` | Builds or upgrades Solar Plant. | Delay in seconds |
| `acs` | Builds or upgrades the Alliance Combat System. | Delay in seconds |

### Research Commands

All research commands follow the same pattern as building commands - use the keyword for the technology you want to research.

**Note:** Research commands are processed through the same macro system as buildings. To research a specific technology, you can use its keyword (e.g., `energy`, `computer`, `espionage`, etc.). Refer to the GID mapping in `utils.php` for available keywords.

### Utility Commands

| Command | Description | Returns |
|---------|-------------|---------|
| `BotExec('strategy_name')` | Executes another bot strategy in parallel. Returns 1 if successful, 0 if failed. | 1 or 0 |
| `BotSetVar('var_name', value)` | Sets a bot variable for persistent storage across strategy executions. | void |

### Custom Eval Commands

In addition to predefined macros, you can use PHP code directly in action blocks using `eval()`. The interpreter will execute any valid PHP expression.

**Example:**
```php
BotResourceSettings(100, 100, 50, 100, 100, 100)
```

This sets resource production percentages (Metal, Crystal, Deuterium, Solar, Fusion, Satellite).

---

## Condition Block Commands

Condition blocks evaluate to `true` or `false` and determine which branch the bot follows (Yes/No/Probability branches).

### Predefined Conditions

These are keywords that check common bot states:

| Condition | Description | Returns |
|-----------|-------------|---------|
| `basicdone` | Checks if basic infrastructure is complete (Metal Mine ≥4, Crystal Mine ≥2, Deut Synth ≥1, Research Lab ≥1). | true/false |
| `hasnanitetech` / `hasnanitefactory` | Checks if Nanite Factory technology requirements are met. | true/false |
| `allships` / `hasallships` / `personalityships` | Checks if all ships defined in the bot's personality configuration have been unlocked. | true/false |
| `canexpedition` | Checks if the bot has enough deuterium to send all ships on an expedition. | true/false |
| `idle` | Checks if the bot should idle (insufficient energy and cannot build Solar Plant, or both build and research queues are full). | true/false |
| `allbuildings` | Checks if all buildings (appropriate for planet/moon) have been unlocked. | true/false |
| `basicresearch` | Checks if basic research is complete (Energy 3, Combustion Drive 3, Impulse Drive 3, Espionage 2, Computer 2). | true/false |

### Level Conditions

Check if a building or research has reached a specific level using the syntax `keyword:level`:

**Syntax:** `building_name:level`

**Examples:**
- `shipyard:2` - Checks if Shipyard is level 2 or higher
- `robotics:5` - Checks if Robotics Factory is level 5 or higher
- `reslab:10` - Checks if Research Lab is level 10 or higher
- `energy:8` - Checks if Energy Technology is level 8 or higher

You can use any building or research keyword from the GID mapping system (see `utils.php` for complete list).

### Personality Conditions

Check if the bot matches a specific personality type:

**Syntax:** `personality:type`

**Examples:**
- `personality:miner` - Checks if the bot has the "miner" personality
- `personality:fleeter` - Checks if the bot has the "fleeter" personality

### Custom Eval Conditions

You can use any valid PHP expression that returns a boolean value. The interpreter will evaluate it using `eval()`.

**Examples:**
```php
BotGetBuild(21) >= 10  // Check if Shipyard (GID 21) is level 10+
BotGetResearch(113) < 5  // Check if Energy Tech (GID 113) is below level 5
BotEnergyAbove(1000)  // Check if planet energy is above 1000
```

---

## Built-in API Functions

Backwards maintainability enables the original functions to be used in custom eval blocks to provide granular bot control (both action and condition):

### Building Functions

| Function | Description | Example |
|----------|-------------|---------|
| `BotCanBuild($gid)` | Checks if a building can be constructed (returns true/false). | `BotCanBuild(21)` |
| `BotBuild($gid)` | Starts building construction. Returns build time in seconds or 0 if failed. | `BotBuild(21)` |
| `BotGetBuild($gid)` | Gets the current level of a building. | `BotGetBuild(21)` |

### Research Functions

| Function | Description | Example |
|----------|-------------|---------|
| `BotCanResearch($gid)` | Checks if a technology can be researched (returns true/false). | `BotCanResearch(113)` |
| `BotResearch($gid)` | Starts researching a technology. Returns research time in seconds or 0 if failed. | `BotResearch(113)` |
| `BotGetResearch($gid)` | Gets the current level of a research. | `BotGetResearch(113)` |

---

## Common GID (Game ID) Reference

Here are commonly used GIDs for buildings and research:

### Buildings
- Metal Mine: `1` or `GID_B_METAL_MINE`
- Crystal Mine: `2` or `GID_B_CRYS_MINE`
- Deuterium Synthesizer: `3` or `GID_B_DEUT_SYNTH`
- Solar Plant: `4` or `GID_B_SOLAR`
- Fusion Reactor: `12` or `GID_B_FUSION`
- Robotics Factory: `14` or `GID_B_ROBOTS`
- Nanite Factory: `15` or `GID_B_NANITES`
- Shipyard: `21` or `GID_B_SHIPYARD`
- Research Lab: `31` or `GID_B_RES_LAB`
- Missile Silo: `44` or `GID_B_MISS_SILO`
- Terraformer: `33` or `GID_B_TERRAFORMER`

### Research
- Energy Technology: `113` or `GID_R_ENERGY`
- Laser Technology: `120` or `GID_R_LASER`
- Ion Technology: `121` or `GID_R_ION`
- Hyperspace Technology: `114` or `GID_R_HYPERSPACE`
- Combustion Drive: `115` or `GID_R_COMBUST_DRIVE`
- Impulse Drive: `117` or `GID_R_IMPULSE_DRIVE`
- Hyperspace Drive: `118` or `GID_R_HYPERSPACE_DRIVE`
- Espionage Technology: `106` or `GID_R_ESPIONAGE`
- Computer Technology: `108` or `GID_R_COMPUTER`

**For a complete GID reference, see `game/id.php` and `game/bot/utils.php`.**

---

## Tips and Best Practices

1. **Use Predefined Macros:** They handle common tasks automatically and include error checking.

2. **Check Conditions First:** Always use condition blocks to verify prerequisites before executing actions.

3. **Handle Delays:** Action blocks return delay times in seconds. The bot system automatically schedules the next execution.

4. **Probability Branches:** Condition blocks can have probability branches (e.g., "50%") for random behavior.

5. **Debug Your Strategies:** Enable debug logging in `interpreter.php` to see detailed execution logs.

6. **Use Bot Variables:** Store persistent state across strategy executions with `BotGetVar()` and `BotSetVar()`.

7. **Parallel Strategies:** Use `BotExec()` to run multiple strategies simultaneously (e.g., building and fleet production).

8. **Energy Management:** Many macros automatically check and build Solar Plants when energy is insufficient.

---

## Example Strategy Patterns

### Simple Mining Loop
```
[Start] → [Condition: basicdone]
    ├─ Yes → [Action: mines] → [Back to Condition]
    └─ No → [Action: basic] → [Back to Condition]
```

### Tech Progression
```
[Start] → [Condition: shipyard:2]
    ├─ Yes → [Action: research] → [Action: sleep]
    └─ No → [Action: tech] → [Action: sleep]
```

### Random Behavior
```
[Start] → [Condition: hasnanitetech]
    ├─ Yes (50%) → [Action: mines]
    ├─ Yes (50%) → [Action: research]
    └─ No → [Action: tech]
```

---

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

## Version Information

This reference is for the bot system as of December 2025.