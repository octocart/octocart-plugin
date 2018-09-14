<?php namespace Xeor\OctoCart\Controllers;

use Flash;
use Config;
use Redirect;
use BackendMenu;
use Backend\Classes\Controller;

/**
 * Product Attributes Back-end Controller
 */
class ProductAttributes extends Controller
{

    public $implement = [
        'Backend.Behaviors.ImportExportController',
    ];

    public $importExportConfig = 'config_import_export.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Xeor.OctoCart', 'products', 'products');
    }
}