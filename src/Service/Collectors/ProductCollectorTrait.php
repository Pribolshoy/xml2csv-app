<?php


namespace App\Service\Collectors;


trait ProductCollectorTrait
{
    protected function collectProducts($products, $categories) {
        $product_data = [];

        foreach($categories as $category_id => $category) {
            foreach($category as $subcategory) {
                foreach($products as $parent_product) {
                    $is_in = false;
                    if ($parent_product['products']) {
                        foreach($parent_product['products'] as $product_id => $product) {
                            if (in_array($product_id, $subcategory['products'])) {
                                if ($is_in) {
                                    $product_data[$category_id][$parent_product['uid']]['products'][$product_id] = $product;
                                } else {
                                    $product_data[$category_id][$parent_product['uid']] = $parent_product;
                                    $product_data[$category_id][$parent_product['uid']]['category_name'] = $subcategory['name'];
                                    $product_data[$category_id][$parent_product['uid']]['products'] = [];
                                    $product_data[$category_id][$parent_product['uid']]['products'][$product_id] = $product;
                                }
                                $is_in = true;
                            }
                        }
                    }
                }
            }
        }

        return $product_data;
    }
}