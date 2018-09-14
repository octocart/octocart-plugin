<?php namespace Xeor\OctoCart\Components;

use Log;
use Input;
use OctoCart;
use Redirect;
use Cms\Classes\ComponentBase;
use Xeor\OctoCart\Models\Settings;
use Xeor\OctoCart\Models\Category;
use Xeor\OctoCart\Models\Product as ProductModel;

class Products extends ComponentBase
{

    /**
     * A collection of products to display
     * @var Collection
     */
    public $products;

    /**
     * Parameter to use for the page number
     * @var string
     */
    public $pageParam;

    /**
     * If the product list should be filtered by a category, the model to use.
     * @var Model
     */
    public $category;

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
     * If the product list should be ordered by another attribute.
     * @var string
     */
    public $sortOrder;

    public function componentDetails()
    {
        return [
            'name'        => 'xeor.octocart::lang.products.name',
            'description' => 'xeor.octocart::lang.products.description'
        ];
    }

    public function defineProperties()
    {
        return [
            'pageNumber' => [
                'title'       => 'xeor.octocart::lang.products.pagination',
                'description' => 'xeor.octocart::lang.products.pagination_description',
                'type'        => 'string',
                'default'     => '{{ :page }}',
            ],
            'categoryFilter' => [
                'title'       => 'xeor.octocart::lang.products.filter',
                'description' => 'xeor.octocart::lang.products.filter_description',
                'type'        => 'string',
                'default'     => ''
            ],
            'productsPerPage' => [
                'title'             => 'xeor.octocart::lang.products.products_per_page',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'xeor.octocart::lang.products.products_per_page_validation',
                'default'           => '10',
            ],
            'noProductsMessage' => [
                'title'        => 'xeor.octocart::lang.products.no_products',
                'description'  => 'xeor.octocart::lang.products.no_products_description',
                'type'         => 'string',
                'default'      => 'No products found',
                'showExternalParam' => false
            ],
            'sortOrder' => [
                'title'       => 'xeor.octocart::lang.products.order',
                'description' => 'xeor.octocart::lang.products.order_description',
                'type'        => 'dropdown',
                'default'     => 'created_at desc'
            ],
            'promote' => [
                'title'       => 'xeor.octocart::lang.product.promote',
                'type'        => 'checkbox',
                'default'     => false
            ],

        ];
    }

    public function getSortOrderOptions()
    {
        return ProductModel::$allowedSortingOptions;
    }

    public function onRun()
    {
        $this->prepareVars();

        $this->category = $this->page['category'] = $this->loadCategory();
        $this->products = $this->page['products'] = $this->listProducts();

        /*
         * If the page number is not valid, redirect
         */
        if ($pageNumberParam = $this->paramName('pageNumber')) {
            $currentPage = $this->property('pageNumber');

            if ($currentPage > ($lastPage = $this->products->lastPage()) && $currentPage > 1)
                return Redirect::to($this->currentPageUrl([$pageNumberParam => $lastPage]));
        }
    }

    protected function prepareVars()
    {
        $this->pageParam = $this->page['pageParam'] = $this->paramName('pageNumber');
        $this->noProductsMessage = $this->page['noProductsMessage'] = $this->property('noProductsMessage');

        /*
         * Page links
         */
        $this->productDisplayPage = $this->page['productDisplayPage'] = Settings::get('product_display_page', 'product');
        $this->categoryPage = $this->page['categoryPage'] = Settings::get('category_page', 'category');;
        $this->cartPage = $this->page['cartPage'] = Settings::get('redirect_user_after_add_to_cart', 'checkout');
    }

    protected function listProducts()
    {
        $category = $this->category ? $this->category->id : null;

        /*
         * List all the products, eager load their categories
         */
        $products = ProductModel::with('categories')->listFrontEnd([
            'page'       => $this->property('pageNumber'),
            'sort'       => $this->property('sortOrder'),
            'perPage'    => $this->property('productsPerPage'),
            'category'   => $category,
            'search'     => isset($_GET['search']) ? $_GET['search'] : '',
            'promote'    => $this->property('promote'),
        ]);

        /*
         * Add a "url" helper attribute for linking to each product and category
         */
        $products->each(function($product) {
            $product->setUrl($this->productDisplayPage, $this->controller);

            $product->categories->each(function($category) {
                $category->setUrl($this->categoryPage, $this->controller);
            });
        });

        return $products;
    }

    protected function loadCategory()
    {
        if (!$categoryId = $this->property('categoryFilter'))
            return null;

        if (!$category = Category::whereSlug($categoryId)->first())
            return null;

        return $category;
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
            Log::warning('OctoCart: Products - onAddToCart().');
        }
    }

}