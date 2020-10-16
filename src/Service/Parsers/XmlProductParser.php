<?php


namespace App\Service\Parsers;

use Symfony\Component\Config\Definition\Exception\Exception;

class XmlProductParser
{
    public function run($document) {
        // Собрать продукты
        $product_data = [];

        if (isset($document->product[0])) {
            $i = 1;
            foreach($document->product as $product) {

                if ($i > 30) {
//                    break;
                }

                $parent_product = $this->getParentProduct($product);
                if (!isset($product_data[$parent_product['uid']])) {
                    $product_data[$parent_product['uid']] = $parent_product;
                }

                $sub_product = $this->getProduct($product);
                $product_data[$sub_product['parent_uid']]['products'][$sub_product['uid']] = $sub_product;

                $i++;
            }
        } else {
            throw new Exception('Товарные позиции в xml файле не обаружены');
        }
        return $product_data;
    }

    protected function getParentProduct($product) {
        if ((string)$product->group) {
            $is_parent = true;
            $uid = (string)$product->group;
        } else {
            $is_parent = false;
            $uid = (string)$product->product_id;
        }
        $brand = (string)$product->brand;
        $title = explode(',', (string)$product->name)[0];
        $text = (string)$product->content;
        $photo = (string)$product->super_big_image['src'];
        $weight = (string)$product->weight;

        // characteristics
        if (isset($product->matherial)) {
            $matherial = (string)$product->matherial;
        } else {
            $matherial = '';
        }

        if (isset($product->product_size)) {
            $product_size = (string)$product->product_size;
        } else {
            $product_size = '';
        }

        if (isset($product->print[0])) {
            $print = '';
            foreach ($product->print as $item) {
                $print .= (string)$item->name . '-' . (string)$item->description;
                $print .= ', ';
            }
            $print = trim($print, ', ');
        } else {
            $print = '';
        }

        $product = [
            'uid' => $uid,
            'is_parent' => $is_parent,
            'brand' => $brand,
            'title' => $title,
            'text' => $text,
            'weight' => $weight,
            'matherial' => $matherial,
            'print' => $print,
            'product_size' => $product_size,
            'photo' => $photo,
        ];

        return $product;
    }

    protected function getProduct($product) {

        if ((string)$product->group) {
            $uid = (string)$product->group;
        } else {
            $uid = (string)$product->product_id;
        }

        $product_id = (string)$product->product_id;
        $sku = (string)$product->code;
        $title = (string)$product->name;
        $price = (string)$product->price->price;
        $quantity = '';

        if ($q = preg_match_all("#,{1,}#", $title)) {
            $parts = explode(',', $title);
            if ($q === 1) {
                $editions = $parts[1];
            } else {
                unset($parts[0]);
                $editions = implode(',', $parts);
            }

            $editions = trim($editions);
        } else {
            $editions = $title;
        }

        $photo = (string)$product->super_big_image['src'];

        $product = [
            'parent_uid' => $uid,
            'uid' => $product_id,
            'title' => $title,
            'sku' => $sku,
            'price' => $price,
            'quantity' => $quantity,
            'editions' => $editions,
            'photo' => $photo,
        ];

        return $product;
    }
}