<?php


namespace App\Service;

use Symfony\Component\Config\Definition\Exception\Exception;

class CsvStockMaker extends BaseCsvMaker
{

    public function run() {

        if (!$this->document) {
            throw new Exception('Не загружен массив с продуктами!');
        }

        if (!$this->output_dir) {
            throw new Exception('Не установлена директория для сохранения!');
        }

        $string = '';
        $string .= $this->setFirstRow();

        foreach( $this->document as $sku => $quantity ) {
            $cols = [
                $sku,
                $quantity,
            ];

            $string .= implode( $this->col_delimiter, $cols ) . $this->row_delimiter; // добавляем строку в данные
        }
        $string = rtrim( $string, $this->row_delimiter );

        if( $this->output_dir ) {
            // создаем csv файл и записываем в него строку
            file_put_contents( $this->output_dir . 'stock.csv', $string );
        }
    }

    protected function setFirstRow() {
        $string = '';

        $cols = [
            'SKU',
            'Quantity',
        ];

        $string .= implode( $this->col_delimiter, $cols ) . $this->row_delimiter; // добавляем строку в данные
        return $string;
    }
}