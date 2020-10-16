<?php


namespace App\Service;


use Symfony\Component\Config\Definition\Exception\Exception;

class CsvCatalogMaker extends BaseCsvMaker
{

    public function run() {

        if (!$this->document) {
            throw new Exception('Не загружен массив с продуктами!');
        }

        if (!$this->output_dir) {
            throw new Exception('Не установлена директория для сохранения!');
        }

        foreach( $this->document as $category_id => $category_products ) {
            // Создать csv документ
            // строка, которая будет записана в csv файл
            $string = '';

            $string .= $this->setFirstRow();

            // перебираем все данные
            foreach( $category_products as $parent_product ) {

                if ($parent_product['is_parent']) {
                    $string .= $this->addGroupRow($parent_product);
                } else {
                    $string .= $this->addCommonRow($parent_product);
                }
            }

            $string = rtrim( $string, $this->row_delimiter );

            if( $this->output_dir ) {
                // создаем csv файл и записываем в него строку
                file_put_contents( $this->output_dir . $category_id . '.csv', $string );
            }
        }
    }

    protected function setFirstRow() {
        $string = '';

        $cols = [
            'Tilda UID',
            'Brand',
            'SKU',
            'Category',
            'Title',
            'Text',
            'Photo',
            'Price',
            'Quantity',
            'Editions',
            'Parent UID',
            'Characteristics:Материал',
            'Characteristics:Виды нанесения',
            'Characteristics:Размеры',
            'Weight'
        ];

        $string .= implode( $this->col_delimiter, $cols ) . $this->row_delimiter; // добавляем строку в данные
        return $string;
    }

    protected function addCommonRow($parent_product) {
        $string = '';

        if ($parent_product['products']) {
            foreach( $parent_product['products'] as $product ) {
                $cols = [
                    $parent_product['uid'],
                    $parent_product['brand'],
                    $product['sku'],
                    $parent_product['category_name'],
                    $this->processCsvCol($parent_product['title']),
                    $this->processCsvCol($parent_product['text']),
                    $parent_product['photo'],
                    $product['price'],
                    $product['quantity'],
                    $product['editions'],
                    '', //Parent UID
                    $this->processCsvCol($parent_product['matherial']),
                    $this->processCsvCol($parent_product['print']),
                    $this->processCsvCol($parent_product['product_size']),
                    $parent_product['weight']
                ];
            }
        }

        $string .= implode( $this->col_delimiter, $cols ) . $this->row_delimiter; // добавляем строку в данные
        return $string;
    }

    protected function addGroupRow($parent_product) {
        $string = '';

        $cols = [
            $parent_product['uid'],
            $parent_product['brand'],
            '', // SKU
            $parent_product['category_name'],
            $this->processCsvCol($parent_product['title']),
            $this->processCsvCol($parent_product['text']),
            $parent_product['photo'],
            '', //Price
            '', //Quantity
            '', //Editions
            '', //Parent UID
            $this->processCsvCol($parent_product['matherial']),
            $this->processCsvCol($parent_product['print']),
            $this->processCsvCol($parent_product['product_size']),
            $parent_product['weight']
        ];

        $string .= implode( $this->col_delimiter, $cols ) . $this->row_delimiter; // добавляем строку в данные

        if ($parent_product['products']) {
            foreach( $parent_product['products'] as $product ) {
                $string .= $this->addChildRow($product);
            }
        }

        return $string;
    }

    protected function addChildRow($product) {
        $string = '';

        $cols = [
            $product['uid'],
            '', // Brand
            $product['sku'],
            '', // Category
            $this->processCsvCol($product['title']),
            '', // Text
            $product['photo'],
            $product['price'],
            $product['quantity'],
            $product['editions'],
            $product['parent_uid'],
            '', //matherial
            '', //print
            '', //product_size
            '', // weight
        ];

        $string .= implode( $this->col_delimiter, $cols ) . $this->row_delimiter; // добавляем строку в данные
        return $string;
    }
}