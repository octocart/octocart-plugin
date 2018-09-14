<?php namespace Xeor\OctoCart\Controllers;

use Flash;
use BackendMenu;
use Backend\Classes\Controller;
use Xeor\OctoCart\Models\Category;

/**
 * Categories Back-end Controller
 */
class Categories extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
        'Backend.Behaviors.ReorderController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';
    public $reorderConfig = 'config_reorder.yaml';

    public $requiredPermissions = ['xeor.octocart.access_categories'];

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Xeor.OctoCart', 'products', 'categories');
    }

    public function index_onDelete()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {

            foreach ($checkedIds as $categoryId) {
                if ((!$category = Category::find($categoryId)))
                    continue;

                $category->delete();
            }

            Flash::success('Successfully deleted those categories.');
        }

        return $this->listRefresh();
    }

}