<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\Infrastructure\Security;

use DrSoftFr\Module\ProductWizard\Application\Port\Security\HtmlSanitizerInterface;

final class HtmlSanitizer implements HtmlSanitizerInterface
{
    public function sanitize(?string $html): ?string
    {
        return self::sanitizeStatic($html);
    }

    /**
     * Sanitizes the given HTML input by removing or neutralizing potentially harmful content.
     *
     * @param string|null $html The HTML input to sanitize. Can be null or an empty string.
     *
     * @return string|null The sanitized HTML string, or null if the input was null or an empty string.
     */
    public static function sanitizeStatic(?string $html): ?string
    {
        if (null === $html) {
            return null;
        }

        $raw = trim((string)$html);

        // 1) Normalize NBSP & co. EARLY to prevent &nbsp; from breaking line breaks
        $raw = self::normalizeNbsp($raw);

        // Considers content that does not contain visible text (tags alone, spaces, &nbsp;, etc.) to be “empty.”
        if (self::isHtmlVisuallyEmpty($raw)) {
            return null;
        }

        // 2) Purify
        $clean = \Tools::purifyHTML($raw, null, true);

        // 3) Re-normalize in case purify has re-encoded
        $clean = self::normalizeNbsp($clean);

        // Revalidate after purification to handle cases such as <p><br></p>, &nbsp;, etc.
        if (self::isHtmlVisuallyEmpty($clean)) {
            return null;
        }

        return $clean;
    }

    /**
     * Indicates whether HTML is visually empty (only tags, spaces, NBSP, or zero-width characters).
     */
    private static function isHtmlVisuallyEmpty(string $html): bool
    {
        if ('' === $html) {
            return true;
        }

        // Normalize NBSPs and zero-width characters
        $normalized = preg_replace('/\x{00A0}|\x{200B}|\x{200C}|\x{200D}|\x{FEFF}|&nbsp;/u', ' ', $html);

        // Remove tags and decode HTML entities to keep only visible text
        $text = trim(html_entity_decode(strip_tags((string)$normalized), ENT_QUOTES | ENT_HTML5));

        return '' === $text;
    }

    /**
     * Replaces all NBSP/zero-width variants with single spaces.
     * Does NOT compact spaces (no loss of alignment).
     */
    private static function normalizeNbsp(string $html): string
    {
        // 1) Decode & and &#160; as U+00A0
        $decoded = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // 2) Replaces NBSP + zero-width characters with a normal space
        //    \x{00A0} NBSP, \x{200B}/\x{200C}/\x{200D} zero-width, \x{FEFF} BOM
        return preg_replace('/[\x{00A0}\x{200B}\x{200C}\x{200D}\x{FEFF}]/u', ' ', $decoded);
    }
}
