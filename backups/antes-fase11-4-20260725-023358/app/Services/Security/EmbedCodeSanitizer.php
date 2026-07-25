<?php

namespace App\Services\Security;

use DOMDocument;
use DOMElement;
use DOMNode;

final class EmbedCodeSanitizer
{
    /** @var list<string> */
    private const ALLOWED_HOSTS = [
        'youtube.com',
        'www.youtube.com',
        'youtube-nocookie.com',
        'www.youtube-nocookie.com',
        'facebook.com',
        'www.facebook.com',
        'web.facebook.com',
        'tiktok.com',
        'www.tiktok.com',
        'dlive.tv',
        'www.dlive.tv',
        'player.dlive.tv',
    ];

    /** @var list<string> */
    private const ALLOWED_IFRAME_ATTRIBUTES = [
        'src', 'width', 'height', 'title', 'frameborder', 'allow',
        'allowfullscreen', 'loading', 'referrerpolicy', 'class',
    ];

    public static function sanitize(?string $html): string
    {
        $html = trim((string) $html);

        if ($html === '') {
            return '';
        }

        $previous = libxml_use_internal_errors(true);
        $document = new DOMDocument('1.0', 'UTF-8');
        $loaded = $document->loadHTML(
            '<?xml encoding="utf-8" ?><div id="embed-root">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (! $loaded) {
            return '';
        }

        $root = $document->getElementById('embed-root');

        if (! $root) {
            return '';
        }

        self::removeUnsafeNodes($root);

        $output = '';
        foreach ($root->childNodes as $child) {
            $output .= $document->saveHTML($child) ?: '';
        }

        return trim($output);
    }

    private static function removeUnsafeNodes(DOMNode $root): void
    {
        $children = [];
        foreach ($root->childNodes as $child) {
            $children[] = $child;
        }

        foreach ($children as $child) {
            if (! $child instanceof DOMElement) {
                $root->removeChild($child);
                continue;
            }

            if (strtolower($child->tagName) !== 'iframe' || ! self::sanitizeIframe($child)) {
                $root->removeChild($child);
                continue;
            }
        }
    }

    private static function sanitizeIframe(DOMElement $iframe): bool
    {
        $source = trim($iframe->getAttribute('src'));

        if (! self::isAllowedSource($source)) {
            return false;
        }

        $attributeNames = [];
        foreach ($iframe->attributes as $attribute) {
            $attributeNames[] = strtolower($attribute->name);
        }

        foreach ($attributeNames as $attributeName) {
            if (! in_array($attributeName, self::ALLOWED_IFRAME_ATTRIBUTES, true)) {
                $iframe->removeAttribute($attributeName);
            }
        }

        $iframe->setAttribute('src', $source);
        $iframe->setAttribute('loading', 'lazy');
        $iframe->setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');

        return true;
    }

    private static function isAllowedSource(string $source): bool
    {
        if ($source === '' || preg_match('/[\x00-\x1F\x7F]/', $source)) {
            return false;
        }

        $parts = parse_url($source);

        if (! is_array($parts) || strtolower((string) ($parts['scheme'] ?? '')) !== 'https') {
            return false;
        }

        $host = strtolower(rtrim((string) ($parts['host'] ?? ''), '.'));

        return in_array($host, self::ALLOWED_HOSTS, true);
    }
}
