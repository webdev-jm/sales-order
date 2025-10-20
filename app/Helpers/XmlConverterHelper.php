<?php

namespace App\Helpers;

class XmlConverterHelper {

    public function arrayToXml($data, $name, $program = NULL) {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;  // Enable formatting and indentation

        $parent = $dom->createElement($name);
        $parent->setAttribute('xmlns:xsd', 'http://www.w3.org/2001/XMLSchema-instance');
        $parent->setAttribute('xsd:noNamespaceSchemaLocation', $program);
        $dom->appendChild($parent);

        $this->arrayToXmlHelper($data, $dom, $parent);

        return $dom->saveXML();
    }

    private function arrayToXmlHelper($data, $dom, &$parent) {
        foreach ($data as $key => $value) {
            // Handle numeric keys by using a generic element name
            $elementName = is_numeric($key) ? 'item' : $key;
            if (is_array($value)) {
                $subNode = $dom->createElement($elementName);
                $parent->appendChild($subNode);
                $this->arrayToXmlHelper($value, $dom, $subNode);
            } else {
                $child = $dom->createElement($elementName, htmlspecialchars((string)$value));
                $parent->appendChild($child);
            }
        }
    }
}
