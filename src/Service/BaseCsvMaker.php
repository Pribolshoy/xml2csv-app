<?php


namespace App\Service;

use Symfony\Component\Config\Definition\Exception\Exception;

abstract class BaseCsvMaker
{
    protected $output_dir;
    protected $document;
    protected $col_delimiter;
    protected $row_delimiter;

    public function __construct()
    {
        $this->col_delimiter = ',';
        $this->row_delimiter = "\r\n";
    }

    abstract public function run();

    protected function processCsvCol($string) {
        // строки должны быть в кавычках ""
        // кавычки " внутри строк нужно предварить такой же кавычкой "
        if( preg_match('/[",;\r\n]/', $string) ) {
            // поправим перенос строки
            if( $this->row_delimiter === "\r\n" ) {
                $string = str_replace( "\r\n", '\n', $string );
                $string = str_replace( "\r", '', $string );
            } elseif( $this->row_delimiter === "\n" ) {
                $string = str_replace( "\n", '\r', $string );
                $string = str_replace( "\r\r", '\r', $string );
            }

            $string = str_replace( '"', '""', $string ); // предваряем "
            $string = '"'. $string .'"'; // обрамляем в "
        }

        return $string;
    }

    public function setOutputdir($output_dir) {
        $this->output_dir = $output_dir;
    }

    public function setDocument($document) {
        $this->document = $document;
    }
}