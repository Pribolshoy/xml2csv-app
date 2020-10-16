<?php


namespace App\Service\Parsers;

use Symfony\Component\Config\Definition\Exception\Exception;

class XmlStockParser
{
    public function run($document) {
        $stock_data = [];

        if (isset($document->stock[0])) {
            foreach($document->stock as $product) {
                $stock_data[(string)$product->code] = (string)$product->amount;
            }
        } else {
            throw new Exception('Товарные позиции в stock.xml не обаружены');
        }
        return $stock_data;
    }
}