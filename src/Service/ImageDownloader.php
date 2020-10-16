<?php


namespace App\Service;


use Symfony\Component\Config\Definition\Exception\Exception;

class ImageDownloader
{
    protected $project_dir;
    protected $source_path;
    protected $products;
    protected $images = [];

    public function run() {
        $this->collectImageUrls();
        $this->removeExistsImages();
        $this->downloadImages();
    }

    protected function collectImageUrls() {
        if (!$this->products) {
            throw new Exception('Не загружен список товаров!');
        }

        foreach ($this->products as $category_products) {
            foreach ($category_products as $parent_product) {
                if (!in_array($parent_product['photo'], $this->images)) {
                    $this->images[] = $parent_product['photo'];
                }

                foreach ($parent_product['products'] as $product) {
                    if (!in_array($product['photo'], $this->images)) {
                        $this->images[] = $product['photo'];
                    }
                }
            }
        }
    }

    protected function removeExistsImages() {
        if (!$this->project_dir) {
            throw new Exception('Не указана директория проекта (project_dir)!');
        }

        foreach ($this->images as $key => &$image) {
            if (file_exists($this->project_dir .'public' . DIRECTORY_SEPARATOR . $image)) {
                unset($this->images[$key]);
            }
        }
    }

    protected function downloadImages() {
        if (!$this->source_path) {
            throw new Exception('Не указана ссылка для скачивания изображения (source_path)!');
        }

        $i = 0;
        foreach ($this->images as $image) {
            $path = pathinfo($image);
            $dirname = $this->project_dir . DIRECTORY_SEPARATOR .'public' . DIRECTORY_SEPARATOR . $path['dirname'];

            if (!is_dir($dirname)) {
                mkdir($dirname, 0777, true);
            }

            $dest = $this->project_dir . DIRECTORY_SEPARATOR .'public' . DIRECTORY_SEPARATOR . $image;

            if (is_int($i/5)) {
                sleep(1);
            }
            copy($this->source_path . $image, $dest);

            $i++;
        }
    }

    public function setProjectDir($project_dir) {
        $this->project_dir = $project_dir;
    }

    public function setSourcePath($source_path) {
        $this->source_path = $source_path;
    }

    public function setProducts($products) {
        $this->products = $products;
    }
}