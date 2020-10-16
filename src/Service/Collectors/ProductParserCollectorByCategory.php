<?php


namespace App\Service\Collectors;


class ProductParserCollectorByCategory extends BaseParserCollector
{
    use ProductCollectorTrait, CategoryCollectorTrait;

    protected $config;
    protected $products;
    protected $categories;

    public function run() {
        $this->categories = $this->collectCategories($this->parser->categories, $this->config);
        $this->products = $this->collectProducts($this->parser->products, $this->categories);
    }

    public function setCategories($config) {
        $this->config = $config;
    }

    public function getCategories() {
        if (!$this->categories) {
            $this->run();
        }
        return $this->categories;
    }

    public function getProducts() {
        if (!$this->products) {
            $this->run();
        }
        return $this->products;
    }


}