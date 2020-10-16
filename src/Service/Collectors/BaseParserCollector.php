<?php


namespace App\Service\Collectors;


abstract class BaseParserCollector
{
    protected $parser;

    public function setParser(\App\Service\Parsers\XmlParser $parser) {
        $this->parser = $parser;
    }

    abstract public function run();
}