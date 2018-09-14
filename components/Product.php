<?php namespace Xeor\OctoCart\Components;

use Log;
use Input;
use OctoCart;
use Cms\Classes\ComponentBase;
use Xeor\OctoCart\Models\Settings;
use Xeor\OctoCart\Models\Product as ProductModel;

class Product extends ComponentBase
{

    /**
     * @var Xeor\OctoCart\Models\Product The product model used for display.
     */
    public $product;

    /**
     * Reference to the page name for linking to products.
     * @var string
     */
    public $productDisplayPage;

    /**
     * Reference to the page name for linking to cart.
     * @var string
     */
    public $cartPage;

    /**
     * Reference to the page name for linking to categories.
     * @var string
     */
    public $categoryPage;

    /**
     * An array of products that you recommend instead of the currently viewed product.
     * @var Collection
     */
    public $upSells;

    public function componentDetails()
    {
        return [
            'name'        => 'xeor.octocart::lang.product.name',
            'description' => 'xeor.octocart::lang.product.description'
        ];
    }

    public function defineProperties()
    {
        return [
            'slug' => [
                'title'       => 'xeor.octocart::lang.product.slug',
                'description' => 'xeor.octocart::lang.product.slug_description',
                'default'     => '{{ :slug }}',
                'type'        => 'string'
            ],
        ];
    }

    public function onRun()
    {
        $this->prepareVars();
    }

    protected function prepareVars()
    {

        /*
         * Page links
         */
        $this->productDisplayPage = $this->page['productDisplayPage'] = Settings::get('product_display_page', 'product');
        $this->cartPage = $this->page['cartPage'] = Settings::get('redirect_user_after_add_to_cart', 'cart');
        $this->categoryPage = $this->page['categoryPage'] = Settings::get('categoryPage', 'category');

        $slug = $this->property('slug');
        if ($product = ProductModel::isPublished()->where('slug', $slug)->first()) {
            /*
             * Add a "url" helper attribute for linking to each category
             */
            if ($product->categories->count()) {
                $product->categories->each(function($category){
                    $category->setUrl($this->categoryPage, $this->controller);
                });
            }

            $this->product = $this->page['product'] = $product;
        }
        else {
            $this->setStatusCode(404);
            return $this->controller->run('404');
        }

        $this->upSells = $this->page['upSells'] = $this->loadUpSells();
    }

    protected function loadUpSells()
    {
        $products = [];
        if ($this->product && ($upSells = $this->product->up_sells)) {
            $products = explode(',', $upSells);
            $ids = array_map(function($string) {
                preg_match_all('/#(\d+)\s/', $string, $matches);
                return (int)reset($matches[1]);
            }, $products);
            $products = ProductModel::whereIn('id', $ids)->orderBy('id', 'asc')->get();
            $products->each(function($product) {
                $product->setUrl($this->productDisplayPage, $this->controller);
            });
        }
        return $products;
    }

    public function onAddToCart() {
        $params = Input::all();
        if (isset($params['productId']) && isset($params['quantity']) && is_numeric($params['productId']) && is_numeric($params['quantity'])) {
            $productId = $params['productId'];
            $quantity = $params['quantity'];
            $attributes = [];
            if (isset($params['attributes']) && is_array($params['attributes']) && !empty($params['attributes'])) {
                $attributes = $params['attributes'];
            }
            $cart = OctoCart::add($productId, $quantity, $attributes);
        }
        else {
            Log::warning('OctoCart: Product - onAddToCart().');
        }
    }

}