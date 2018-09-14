<?php namespace Xeor\OctoCart\FormWidgets;

use Backend\Classes\FormWidgetBase;
use Xeor\OctoCart\Models\Product as ProductModel;

class Variation extends FormWidgetBase
{

    /**
     * @inheritDoc
     */
    protected $defaultAlias = 'variation';

    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->fillFromConfig();
    }

    /**
     * @inheritDoc
     */
    public function render()
    {
        $this->prepareVars();
        return $this->makePartial('variation');
    }

    /**
     * Prepares the list data
     */
    public function prepareVars()
    {
        $this->vars['field'] = $this->formField;
        $this->vars['productId'] = $product = $this->getLoadValue();
        $this->vars['products'] = ProductModel::where('type', 'product_variation')->get();
    }

    /**
     * @inheritDoc
     */
    protected function loadAssets()
    {
        $this->addCss('css/variation.css');
    }

    /**
     * @inheritDoc
     */
    public function getSaveValue($value)
    {
        return strlen($value) ? $value : null;
    }
}
