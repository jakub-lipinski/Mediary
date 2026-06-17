<?php

namespace App\Http\Controllers;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;

abstract class Controller
{
    /**
     * @param  array<int, string>  $allowedTags
     */
    protected function sanitizeGeneratedHtml(?string $html, array $allowedTags = ['p', 'br', 'b', 'strong', 'ul', 'ol', 'li']): string
    {
        if (blank($html)) {
            return '';
        }

        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);

        $document->loadHTML('<?xml encoding="UTF-8"><div id="generated-html-root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementById('generated-html-root');

        if (! $root instanceof DOMElement) {
            return e(strip_tags($html));
        }

        $this->sanitizeGeneratedHtmlNode($root, $allowedTags);

        return collect(iterator_to_array($root->childNodes))
            ->map(fn (DOMNode $node): string => $document->saveHTML($node) ?: '')
            ->implode('');
    }

    /**
     * @param  array<int, string>  $allowedTags
     */
    private function sanitizeGeneratedHtmlNode(DOMNode $node, array $allowedTags): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMText) {
                continue;
            }

            if (! $child instanceof DOMElement) {
                $node->removeChild($child);

                continue;
            }

            $tagName = strtolower($child->tagName);

            if (in_array($tagName, ['script', 'style'], true)) {
                $node->removeChild($child);

                continue;
            }

            if (! in_array($tagName, $allowedTags, true)) {
                $node->replaceChild($node->ownerDocument->createTextNode($child->textContent), $child);

                continue;
            }

            while ($child->attributes->length > 0) {
                $child->removeAttributeNode($child->attributes->item(0));
            }

            $this->sanitizeGeneratedHtmlNode($child, $allowedTags);
        }
    }
}
