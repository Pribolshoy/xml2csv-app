<?php

namespace App\Controller;

use App\Service\Parsers\XmlParser;
use App\Service\CsvCatalogMaker;
use App\Service\CsvStockMaker;
use App\Service\ImageDownloader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\KernelInterface;
use App\Service\Collectors\ProductParserCollectorByCategory;

class AppController extends AbstractController
{
    // Vars
    protected $files_dir = 'public' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR;

    //  Services
    protected $parser;
    protected $collector;
    protected $csv_catalog_maker;
    protected $csv_stock_maker;
    protected $downloader;

    public function __construct(XmlParser $xmlParser, ProductParserCollectorByCategory $productCollectorByCategory,
                                CsvCatalogMaker $CsvCatalogMaker, CsvStockMaker $CsvStockMaker, ImageDownloader $downloader)
    {
        $this->parser = $xmlParser;
        $this->collector = $productCollectorByCategory;
        $this->csv_catalog_maker = $CsvCatalogMaker;
        $this->csv_stock_maker = $CsvStockMaker;
        $this->downloader = $downloader;
    }

    protected function download($url) {
        $app_dir = $this->getParameter('kernel.project_dir');

        $xml_name = basename($url);
        $xml_doc = file_get_contents($url);

        if (!is_dir($app_dir . DIRECTORY_SEPARATOR . $this->files_dir)) {
            mkdir($app_dir . DIRECTORY_SEPARATOR . $this->files_dir, 0777, true);
        }

        $handle = fopen($app_dir . DIRECTORY_SEPARATOR . $this->files_dir . $xml_name, 'w');

        if (fwrite($handle, $xml_doc) === FALSE) {
            throw new Exception("Не могу произвести запись в файл ($xml_name)");
        }
        fclose($handle);
    }

    /**
     * @Route("/app", name="app")
     */
    public function index()
    {
        if (!$this->getParameter('app.shop_pass') && !$this->getParameter('app.shop_login')) {
            throw new Exception("Не установлены логин (app.shop_login) и пароль (app.shop_pass) для доступа к серверу. ");
        }

        $config = $this->getParameter('app.parser');

        $catalogue_path = $this->getParameter('app.catalogue_path');
        $stock_path = $this->getParameter('app.stock_path');
        $app_dir = $this->getParameter('kernel.project_dir');

        // Скачать документы xml
        $this->download($catalogue_path);
        $this->download($stock_path);

        // Распарсить документы
        $xml_catalog_name = basename($catalogue_path);
        $xml_stock_name = basename($stock_path);

        $this->parser->setXmlCatalogPath($app_dir . DIRECTORY_SEPARATOR . $this->files_dir . $xml_catalog_name);
        $this->parser->setXmlStockPath($app_dir . DIRECTORY_SEPARATOR . $this->files_dir . $xml_stock_name);
        $this->parser->run();

        // Выбрать необходимые товары
        $this->collector->setParser($this->parser);
        $this->collector->setCategories($config['categories']);
        $this->collector->run();

        $products = $this->collector->getProducts();

        // Скачать изображения на сервер
        $this->downloader->setProducts($products);
        $this->downloader->setProjectDir($app_dir);
        $this->downloader->setSourcePath($this->getParameter('app.api_path'));
        $this->downloader->run();

        // Создать csv файлы
        $this->csv_catalog_maker->setDocument($products);
        $this->csv_catalog_maker->setOutputdir($app_dir . DIRECTORY_SEPARATOR . $this->files_dir);
        $this->csv_catalog_maker->run();

        $this->csv_stock_maker->setDocument($this->parser->stock);
        $this->csv_stock_maker->setOutputdir($app_dir . DIRECTORY_SEPARATOR . $this->files_dir);
        $this->csv_stock_maker->run();


//        print '<pre>';
//        print_r( $this->collector->getCategories() );
//        print_r( $this->collector->getProducts() );
//        print_r( $this->parser->stock );
//        print '</pre>';
//        return;

        return $this->render('app/index.html.twig', []);
    }
}
