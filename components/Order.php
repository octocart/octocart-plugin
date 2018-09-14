<?php namespace Xeor\OctoCart\Components;

use Cms\Classes\ComponentBase;
use Xeor\OctoCart\Models\Settings;
use Xeor\OctoCart\Models\Order as OrderModel;
use Xeor\OctoCart\Models\Product as ProductModel;

class Order extends ComponentBase
{
    /**
     * @var Xeor\OctoCart\Models\Order The order model used for display.
     */
    public $order;

    /**
     * An array of products
     * @var Collection
     */
    public $items;

    /**
     * Reference to the page name for linking to products.
     * @var string
     */
    public $productDisplayPage;

    /**
     * Reference to the page name for linking to categories.
     * @var string
     */
    public $categoryPage;

    public function componentDetails()
    {
        return [
            'name'        => 'xeor.octocart::lang.order.name',
            'description' => 'xeor.octocart::lang.order.description'
        ];
    }

    public function defineProperties()
    {
        return [
            'id' => [
                'title'       => 'xeor.octocart::lang.order.id',
                'description' => 'xeor.octocart::lang.order.id_description',
                'default'     => '{{ :id }}',
                'type'        => 'string'
            ],
        ];
    }

    public function onRun()
    {
        $id = $this->property('id');
        if ($order = OrderModel::find($id)) {
            $order->items = json_decode($order->items, true);
            $this->order = $this->page['order'] = $order;
        }
        else {
            $this->setStatusCode(404);
            return $this->controller->run('404');
        }

        $this->prepareVars();
    }

    protected function prepareVars()
    {
        /*
         * Page links
         */
        $this->productDisplayPage = $this->page['productDisplayPage'] = Settings::get('product_display_page', 'product');
        $this->categoryPage = $this->page['categoryPage'] = Settings::get('category_page', 'category');

        $this->items = $this->page['items'] = $this->listItems();
    }

    protected function listItems()
    {
        $items = $this->order->items;
        foreach ($items as $itemId => $item) {
            $product = ProductModel::find($item['product']);
            $product->setUrl($this->productDisplayPage, $this->controller);
            $product->categories->each(function ($category) {
                $category->setUrl($this->categoryPage, $this->controller);
            });
            $items[$itemId]['product'] = $product;
        }
        return $items;
    }


}