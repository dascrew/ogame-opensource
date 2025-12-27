<?php

declare(strict_types=1);

/**
 * Localized Block Text Mapping System
 *
 * Maps localized predefined block text back to their corresponding macro keywords.
 * This allows the visual designer to use translated text while the interpreter
 * executes the correct macro functions.
 */

require_once __DIR__ . '/../loca.php';

/**
 * Get a mapping of all localized block texts to their macro keywords.
 *
 * This creates a reverse lookup from the display text (in any language)
 * back to the internal macro keyword that should be executed.
 *
 * @param string $lang The language code (e.g., 'en', 'de', 'es')
 * @return array<string, string> Map of localized text => macro keyword
 */
function getLocalizedBlockMapping(string $lang = 'en'): array
{
    global $LOCA;

    // Ensure admin localization is loaded for this language
    if (!isset($LOCA[$lang]['BOT_BLOCK_MINES'])) {
        loca_add('admin', $lang);
    }

    $mapping = [];

    // Define the loca key to macro keyword mapping
    $blockDefinitions = [
        'BOT_BLOCK_MINES' => 'mines',
        'BOT_BLOCK_BASIC' => 'basic',
        'BOT_BLOCK_TECH' => 'tech',
        'BOT_BLOCK_RESEARCH' => 'research',
        'BOT_BLOCK_BUILD' => 'build',
        'BOT_BLOCK_SHIPYARD' => 'shipyard',
        'BOT_BLOCK_ROBOTICS' => 'robotics',
        'BOT_BLOCK_RESLAB' => 'reslab',
        'BOT_BLOCK_NANITE' => 'nanite',
        'BOT_BLOCK_FUSION' => 'fusion',
        'BOT_BLOCK_TERRAFORMER' => 'terraformer',
        'BOT_BLOCK_SILO' => 'silo',
        'BOT_BLOCK_SPEED' => 'speed',
        'BOT_BLOCK_SLEEP' => 'sleep',
    ];

    // Build the mapping for this language
    foreach ($blockDefinitions as $locaKey => $macroKeyword) {
        if (isset($LOCA[$lang][$locaKey])) {
            $localizedText = $LOCA[$lang][$locaKey];
            $mapping[strtolower($localizedText)] = $macroKeyword;
        }
    }

    return $mapping;
}

/**
 * Get all localized block mappings for all supported languages.
 *
 * @return array<string, array<string, string>> Map of lang => (localized text => macro keyword)
 */
function getAllLocalizedBlockMappings(): array
{
    global $Languages;

    $allMappings = [];

    foreach ($Languages as $langCode => $langName) {
        $allMappings[$langCode] = getLocalizedBlockMapping($langCode);
    }

    return $allMappings;
}

/**
 * Normalize block text by checking if it's a localized predefined block.
 * Returns the macro keyword if found, otherwise returns the original text.
 *
 * @param string $block_text The block text from the visual designer
 * @param string $lang The current language
 * @return string Either the macro keyword or the original block text
 */
function normalizeBlockText(string $block_text, string $lang = 'en'): string
{
    $mapping = getLocalizedBlockMapping($lang);
    $lower_text = strtolower(trim($block_text));

    // Check if this text matches a localized block
    if (isset($mapping[$lower_text])) {
        return $mapping[$lower_text];
    }

    // Not a predefined block, return as-is
    return $block_text;
}

/**
 * Check if block text is a localized predefined block in any language.
 *
 * @param string $block_text The block text to check
 * @return bool True if this is a localized predefined block
 */
function isLocalizedBlock(string $block_text): bool
{
    static $allMappings = null;

    if ($allMappings === null) {
        $allMappings = getAllLocalizedBlockMappings();
    }

    $lower_text = strtolower(trim($block_text));

    foreach ($allMappings as $mapping) {
        if (isset($mapping[$lower_text])) {
            return true;
        }
    }

    return false;
}

/**
 * Get the macro keyword for a localized block text in any supported language.
 *
 * This function checks all languages to find a match, making it language-agnostic.
 *
 * @param string $block_text The block text to look up
 * @return string|null The macro keyword if found, null otherwise
 */
function getMacroKeywordFromLocalizedText(string $block_text): ?string
{
    static $allMappings = null;

    if ($allMappings === null) {
        $allMappings = getAllLocalizedBlockMappings();
    }

    $lower_text = strtolower(trim($block_text));

    // Check all languages for a match
    foreach ($allMappings as $mapping) {
        if (isset($mapping[$lower_text])) {
            return $mapping[$lower_text];
        }
    }

    return null;
}
