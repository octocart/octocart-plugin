<?php namespace Xeor\OctoCart\Controllers;

use Db;
use Flash;
use BackendMenu;
use System\Classes\SettingsManager;
use Backend\Classes\Controller;

/**
 * Shipping Back-end Controller
 */
class Shipping extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    //public $requiredPermissions = [];

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('October.System', 'system', 'settings');
        SettingsManager::setContext('Xeor.OctoCart', 'shipping');
    }
}