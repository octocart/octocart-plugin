<?php namespace Xeor\OctoCart\Components;

use Log;
use Input;
use Session;
use OctoCart;
use Cms\Classes\ComponentBase;
use Xeor\OctoCart\Models\Settings;
use Xeor\OctoCart\Models\Product as ProductModel;

class Cart extends ComponentBase
{

    /**
     * An array of products.
     * @var Collection
     */
    public $items;

    /**
     * Message to display when there are no products.
     * @var string
     */
    public $noProductsMessage;

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

    /**
     * Reference to the page name for linking to cart.
     * @var string
     */
    public $cartPage;

    /**
     * Reference to the page name for linking to checkout.
     * @var string
     */
    public $checkoutPage;

    /**
     * The price total.
     * @var float
     */
    public $totalPrice;

    /**
     * The number of items in the cart.
     * @var integer
     */
    public $count;

    /**
     * An array of products that you promote in the cart, based on the current product.
     * @var Collection
     */
    public $crossSells;

    public function componentDetails()
    {
        return [
            'name'        => 'xeor.octocart::lang.cart.name',
            'description' => 'xeor.octocart::lang.cart.description'
        ];
    }

    public function defineProperties()
    {
        return [
            'noProductsMessage' => [
                'title'        => 'xeor.octocart::lang.cart.no_products',
                'description'  => 'xeor.octocart::lang.cart.no_products_description',
                'type'         => 'string',
                'default'      => 'Your cart is currently empty.',
                'showExternalParam' => false
            ],
        ];
    }

    public function onRender()
    {
        $this->prepareVars();
        $this->items = $this->page['items'] = $this->listItems();
        $this->crossSells = $this->page['crossSells'] = $this->loadCrossSells();
    }

    protected function prepareVars()
    {

        $this->noPostsMessage = $this->page['noProductsMessage'] = $this->property('noProductsMessage');

        $this->totalPrice = $this->page['totalPrice'] = OctoCart::total();
        $this->count = $this->page['count'] = OctoCart::count();

        /*
         * Page links
         */
        $this->cartPage = $this->page['cartPage'] = Settings::get('cart_page', 'cart');
        $this->checkoutPage = $this->page['checkoutPage'] = Settings::get('checkout_page', 'checkout');
        $this->productDisplayPage = $this->page['productDisplayPage'] = Settings::get('product_display_page', 'product');
        $this->categoryPage = $this->page['categoryPage'] = Settings::get('category_page', 'category');
    }

    protected function listItems()
    {
        $items = OctoCart::get();
        if (!is_null($items)) {
            foreach ($items as $itemId => $item) {
                $product = ProductModel::find($item['product']);
                $product->setUrl($this->productDisplayPage, $this->controller);
                $product->categories->each(function($category) {
                    $category->setUrl($this->categoryPage, $this->controller);
                });
                $items[$itemId]['product'] = $product;
            }
        }
        return $items;
    }

    protected function loadCrossSells()
    {
        $products = [];
        if ($this->items) {
            foreach ($this->items as $item) {
                $product = $item['product'];
                if ($crossSells = $product->cross_sells) {
                    $products = explode(',', $crossSells);
                    $ids = array_map(function($string) {
                        preg_match_all('/#(\d+)\s/', $string, $matches);
                        return (int)reset($matches[1]);
                    }, $products);
                    $products = ProductModel::whereIn('id', $ids)->orderBy('id', 'asc')->get();
                    $products->each(function($product) {
                        $product->setUrl($this->productDisplayPage, $this->controller);
                    });
                }
            }
        }
        return $products;
    }

    public function onUpdateQuantity() {

        $params = Input::all();
        if (isset($params['itemId']) && isset($params['quantity']) && is_numeric($params['quantity'])) {
            $itemId = $params['itemId'];
            $quantity = $params['quantity'];
            $cart = OctoCart::update($itemId, $quantity);
        }
    }

    public function onRemoveProduct() {
        $params = Input::all();
        if (isset($params['itemId'])) {
            $itemId = $params['itemId'];
            $cart = OctoCart::remove($itemId);
        }
    }

    public function onClear() {
        $cart = OctoCart::clear();
    }

}