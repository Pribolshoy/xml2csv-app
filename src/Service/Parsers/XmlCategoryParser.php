<?php


namespace App\Service\Parsers;


class XmlCategoryParser
{
    public function __construct()
    {
    }

    public function run($document) {
        $category_data = [];

        if (isset($document->page[0])) {
            $i = 1;
            foreach($document->page as $page) {
                $category = $this->getCategoryTree($page);
                $category_data[$category['id']] = $category;
            }
        }

        return $category_data;
    }

    protected function getCategoryTree($page) {

        if (isset($page['parent_page_id'])) {
            $parent_category = (string)$page['parent_page_id'];
        } else {
            $parent_category = 0;
        }

        $page_id = (string)$page->page_id;
        $name = (string)$page->name;
        $uri = (string)$page->uri;

        if (isset($page->page[0])) {
            foreach ($page->page as $subpage) {
                $subcategory = $this->getCategoryTree($subpage);
                $children[$subcategory['id']] = $subcategory;
            }
        } else {
            $children = '';
        }

        if (isset($page->product[0])) {
            foreach ($page->product as $product) {
                $product_id = (string)$product->product;
                $category_id = (string)$product->page;

                $products[$product_id] = [
                    'product_id' => $product_id,
                    'category_id' => $category_id,
                ];
            }
        } else {
            $products = '';
        }


        $result = [
            'id' => $page_id,
            'name' => $name,
            'parent_category' => $parent_category,
            'uri' => $uri,
            'products' => $products,
            'children' => $children,
        ];

        return $result;
    }

}