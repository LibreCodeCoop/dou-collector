<?php

namespace Spatie\ArrayToXml;

use DOMDocument;
use DOMElement;
use DOMException;
use Exception;

class ArrayToXml
{
    protected $document;

    protected $replaceSpacesByUnderScoresInKeyNames = true;

    protected $addXmlDeclaration = true;

    protected $numericTagNamePrefix = 'numeric_';

    public function __construct(
        array $array,
        $rootElement = '',
        $replaceSpacesByUnderScoresInKeyNames = true,
        $xmlEncoding = null,
        $xmlVersion = '1.0',
        $domProperties = []
    ) {}

    public function setNumericTagNamePrefix(string $prefix) {}

    public static function convert(
        array $array,
        $rootElement = '',
        bool $replaceSpacesByUnderScoresInKeyNames = true,
        string $xmlEncoding = null,
        string $xmlVersion = '1.0',
        array $domProperties = []
    ) {}

    public function toXml(): string {}

    public function toDom(): DOMDocument {}

    protected function ensureValidDomProperties(array $domProperties) {}

    public function setDomProperties(array $domProperties) {}

    public function prettify() {}

    public function dropXmlDeclaration() {}

    private function convertElement(DOMElement $element, $value) {}

    protected function addNumericNode(DOMElement $element, $value) {}

    protected function addNode(DOMElement $element, $key, $value){}

    protected function addCollectionNode(DOMElement $element, $value) {}

    protected function addSequentialNode(DOMElement $element, $value) {}

    protected function isArrayAllKeySequential($value) {}

    protected function addAttributes(DOMElement $element, array $data) {}

    protected function createRootElement($rootElement): DOMElement {}

    protected function removeControlCharacters(string $value): string {}
}
