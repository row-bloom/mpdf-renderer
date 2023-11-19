<?php

namespace RowBloom\MpdfRenderer;

use DOMDocument;
use DOMXPath;
use RowBloom\RowBloom\RowBloomException;

class MpdfDom
{
    private DOMDocument $dom;

    private DOMXPath $xPath;

    public static function fromString(string $htmlString): static
    {
        return new static($htmlString);
    }

    final public function __construct(string $htmlString)
    {
        $this->dom = new DOMDocument();
        $this->dom->loadHTML($htmlString);

        $this->xPath = new DOMXPath($this->dom);
    }

    public function translateHeaderFooterClasses(): static
    {
        // ? url title

        return $this->replaceWithTextNode(
            '//*[@class="pageNumber"]',
            '{PAGENO}'
        )->replaceWithTextNode(
            '//*[@class="totalPages"]',
            '{nbpg}'
        )->replaceWithTextNode(
            '//*[@class="date"]',
            date('d/m/Y h:i') // TODO: make it configurable
        );
    }

    /** @see https://www.w3schools.com/xml/xpath_syntax.asp */
    private function replaceWithTextNode(string $xPathQuery, string $newText): static
    {
        $newElement = $this->dom->createTextNode($newText);
        $targetElements = $this->xPath->query($xPathQuery);

        if ($newElement === false) {
            throw new RowBloomException('An error occurred while creating the text node: '.$newText);
        }

        if ($targetElements === false) {
            throw new RowBloomException($xPathQuery.' expression is malformed or the context node is invalid');
        }

        foreach ($targetElements as $targetElement) {
            $targetElement->parentNode->replaceChild($newElement, $targetElement);
        }

        return $this;
    }

    public function toHtml(): string
    {
        $htmlString = $this->dom->saveHTML();

        if ($htmlString === false) {
            throw new RowBloomException('An error occurred while dumping the internal document');
        }

        return $htmlString;
    }
}
