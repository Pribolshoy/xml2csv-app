<?php


namespace App\Service\Collectors;


trait CategoryCollectorTrait
{
    protected $limit;
    protected $counter;

    protected function collectCategories($categories, $config) {
        foreach ($config as $search_category_id => $config_data) {
            if (isset($config_data['limit'])) {
                $this->limit = $config_data['limit'];
                $this->counter = 0;
            }

            foreach ($categories as $category) {
                if ($this->is_over_limit()) {
                    break;
                }
                $category_data[$search_category_id] = $this->processCategoryTree($category, $search_category_id);
            }

            $this->limit = NULL;
        }
        return  $category_data;
    }

    protected function processCategoryTree($category, $id, $collect = false) {
        $category_data = [];

        if ($category['id'] == $id) {
            $collect = true;
        }

        if ($collect) {
            if ($category['products']) {
                foreach ($category['products'] as $product) {
                    if ($this->is_over_limit()) {
                        break;
                    }
                    $category_data[$product['category_id']]['name'] = $category['name'];
                    $category_data[$product['category_id']]['products'][] = $product['product_id'];

                    $this->counter++;
                }
            }
        }

        if ($category['children']) {
            foreach ($category['children'] as $child) {
                if ($this->is_over_limit()) {
                    break;
                }
                $category_data = $category_data + $this->processCategoryTree($child, $id, $collect);
            }
        }

        return $category_data;
    }

    protected function is_over_limit() {
        if ($this->limit && $this->counter >= $this->limit) {
            return true;
        } else {
            return false;
        }
    }
}