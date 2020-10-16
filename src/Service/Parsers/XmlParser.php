<?php


namespace App\Service\Parsers;

use App\Service\Parsers\XmlCategoryParser;
use App\Service\Parsers\XmlProductParser;
use Symfony\Component\Config\Definition\Exception\Exception;

class XmlParser
{
    // Parsers
    protected $category_parser;
    protected $product_parser;
    protected $stock_parser;

    public $products;
    public $categories;
    public $stock;

    public $catalog_document;
    public $stock_document;

    protected $xml_catalog_path = '';
    protected $xml_stock_path = '';

    public function __construct(XmlCategoryParser $XmlCategoryParser, XmlProductParser $XmlProductParser, XmlStockParser $XmlStockParser)
    {
        // Парсер категорий по умолчанию
        $this->category_parser = $XmlCategoryParser;
        $this->product_parser = $XmlProductParser;
        $this->stock_parser = $XmlStockParser;
    }

    public function run() {
        $this->parseCatalog();
        $this->parseStock();
        $this->compilyProductsWithStock();
    }

    public function compilyProductsWithStock() {
        if (!$this->products || !$this->stock) {
            throw new Exception('Один из необходимых документов не был спарсен (products или stock)');
        }

        foreach ($this->products as &$group) {
            if ($group['products']) {
                foreach ($group['products'] as &$product) {
                    if(array_key_exists($product['sku'], $this->stock)) {
                        $product['quantity'] = $this->stock[$product['sku']];
                    }
                }
            }
        }
    }

    public function parseCatalog() {
        if (!$this->xml_catalog_path) {
            throw new Exception('Неоходимо указать путь до файла catalogue.xml');
        }

        $this->catalog_document = new \SimpleXMLElement($this->xml_catalog_path, 0, true);

        $this->products = $this->product_parser->run($this->catalog_document);
        $this->categories = $this->category_parser->run($this->catalog_document);
    }

    public function parseStock() {
        if (!$this->xml_stock_path) {
            throw new Exception('Неоходимо указать путь до файла stock.xml');
        }

        $this->stock_document = new \SimpleXMLElement($this->xml_stock_path, 0, true);
        $this->stock = $this->stock_parser->run($this->stock_document);
    }

    public function setXmlStockPath($xml_stock_path)
    {
        $this->xml_stock_path = $xml_stock_path;
        return $this;
    }

    public function setXmlCatalogPath($xml_catalog_path)
    {
        $this->xml_catalog_path = $xml_catalog_path;
        return $this;
    }

}