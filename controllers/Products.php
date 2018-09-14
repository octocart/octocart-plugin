<?php namespace Xeor\OctoCart\Controllers;

use Db;
use Flash;
use BackendMenu;
use Backend\Classes\Controller;
use Xeor\OctoCart\Models\Product as ProductModel;
use Xeor\OctoCart\Models\ProductAttribute as ProductAttributeModel;

/**
 * Products Back-end Controller
 */
class Products extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
        'Backend.Behaviors.RelationController',
        'Backend.Behaviors.ImportExportController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';
    public $importExportConfig = 'config_import_export.yaml';
    public $relationConfig = 'config_relation.yaml';

    public $requiredPermissions = ['xeor.octocart.access_other_products', 'xeor.octocart.access_products'];

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Xeor.OctoCart', 'products', 'products');
    }

    public function index()
    {
        $this->vars['productsTotal'] = ProductModel::count();
        $this->vars['productsPublished'] = ProductModel::isPublished()->count();
        $this->vars['productsDrafts'] = $this->vars['productsTotal'] - $this->vars['productsPublished'];

        $this->asExtension('ListController')->index();
    }

    public function create()
    {
        $this->addCss('/plugins/xeor/octocart/assets/css/xeor.octocart.css');
        $this->addJs('/plugins/xeor/octocart/assets/js/xeor.octocart.js');
        return $this->asExtension('FormController')->create();
    }

    public function update($recordId = null)
    {
        $this->addCss('/plugins/xeor/octocart/assets/css/xeor.octocart.css');
        return $this->asExtension('FormController')->update($recordId);
    }

    public function index_onDelete()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {

            foreach ($checkedIds as $productId) {
                if ((!$product = ProductModel::find($productId)) || !$product->canEdit($this->user))
                    continue;

                $product->delete();
            }

            Flash::success('Successfully deleted those products.');
        }

        return $this->listRefresh();
    }

    public function index_onDeleteAll()
    {

        ProductModel::where('id', '>', 0)->delete();
        ProductAttributeModel::where('id', '>', 0)->delete();
        Db::table('xeor_octocart_products_categories')->delete();
        Db::table('system_files')->where('attachment_type', 'Xeor\OctoCart\Models\Product')->delete();

        Flash::success('Successfully deleted all products.');

        return $this->listRefresh();
    }

    public function index_onShow()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {

            foreach ($checkedIds as $productId) {
                if ((!$product = ProductModel::find($productId)) || !$product->canEdit($this->user))
                    continue;

                $product->published = 1;
                $product->save();
            }

            Flash::success('Successfully showed those products.');
        }

        return $this->listRefresh();
    }

    public function index_onHide()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {

            foreach ($checkedIds as $productId) {
                if ((!$product = ProductModel::find($productId)) || !$product->canEdit($this->user))
                    continue;

                $product->published = 0;
                $product->save();
            }

            Flash::success('Successfully hidden those products.');
        }

        return $this->listRefresh();
    }

    public function formBeforeCreate($model)
    {
        $model->user_id = $this->user->id;
    }

}