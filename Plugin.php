<?php namespace Xeor\OctoCart;

use App;
use Lang;
use Event;
use Backend;
use System\Classes\PluginBase;
use Xeor\OctoCart\Classes\Helpers;
use Illuminate\Foundation\AliasLoader;
use Request;
use Input;

/**
 * OctoCart Plugin Information File
 */
class Plugin extends PluginBase
{

    /**
     * @var array Plugin dependencies
     */
    public $require = ['RainLab.User'];

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name' => 'xeor.octocart::lang.plugin.name',
            'description' => 'xeor.octocart::lang.plugin.description',
            'author' => 'Sozonov Alexey',
            'icon' => 'icon-shopping-cart',
            'homepage' => 'https://octocart.github.io/'
        ];
    }

    public function registerFormWidgets()
    {
        return [
            'Xeor\OctoCart\FormWidgets\Variation' => [
                'label' => 'Variation',
                'code'  => 'variation'
            ]
        ];
    }

    public function register()
    {
        $alias = AliasLoader::getInstance();
        $alias->alias('OctoCart', 'OctoCart\OctoCart\Facades\Cart');

        App::singleton('cart', function () {
            return new \OctoCart\OctoCart\Cart;
        });
    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return [
            'Xeor\OctoCart\Components\Cart' => 'cart',
            'Xeor\OctoCart\Components\Orders' => 'orders',
            'Xeor\OctoCart\Components\Checkout' => 'checkout',
            'Xeor\OctoCart\Components\Products' => 'products',
            'Xeor\OctoCart\Components\Categories' => 'categories',
            'Xeor\OctoCart\Components\Order' => 'order',
            'Xeor\OctoCart\Components\Product' => 'product',
        ];
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [
            'xeor.octocart.access_products' => [
                'tab' => 'xeor.octocart::lang.access.tab',
                'label' => 'xeor.octocart::lang.access.products'
            ],
            'xeor.octocart.other_products' => [
                'tab' => 'xeor.octocart::lang.access.tab',
                'label' => 'xeor.octocart::lang.access.other_products'
            ],
            'xeor.octocart.access_orders' => [
                'tab' => 'xeor.octocart::lang.access.tab',
                'label' => 'xeor.octocart::lang.access.orders'
            ],
            'xeor.octocart.access_categories' => [
                'tab' => 'xeor.octocart::lang.access.tab',
                'label' => 'xeor.octocart::lang.access.categories'
            ],
            'xeor.octocart.access_import_export' => [
                'tab' => 'xeor.octocart::lang.access.tab',
                'label' => 'xeor.octocart::lang.access.import_export'
            ],
        ];
    }

    /**
     * Registers mail templates for this plugin.
     *
     * @return array
     */
    public function registerMailTemplates()
    {
        return [
            'xeor.octocart::mail.order_confirm_admin' => 'OctoCart Admin email.',
            'xeor.octocart::mail.order_confirm' => 'OctoCart User email.'
        ];
    }

    /**
     * Registers back-end navigation items for this plugin.
     *
     * @return array
     */
    public function registerNavigation()
    {
        return [
            'orders' => [
                'label' => 'xeor.octocart::lang.orders.menu_label',
                'url' => Backend::url('xeor/octocart/orders'),
                'icon' => 'icon-shopping-cart',
                'permissions' => ['xeor.octocart.*'],
                'order' => 100,

                'sideMenu' => [
                    'orders' => [
                        'label' => 'xeor.octocart::lang.orders.menu_label',
                        'url' => Backend::url('xeor/octocart/orders'),
                        'attributes' => ['data-menu-item' => 'orders'],
                        'icon' => 'icon-money',
                        'permissions' => ['xeor.octocart.access_orders'],
                    ],
                ]

            ],
            'products' => [
                'label' => 'xeor.octocart::lang.products.menu_label',
                'url' => Backend::url('xeor/octocart/products'),
                'icon' => 'icon-cubes',
                'permissions' => ['xeor.octocart.*'],
                'order' => 101,

                'sideMenu' => [
                    'products' => [
                        'label' => 'xeor.octocart::lang.products.menu_label',
                        'icon' => 'icon-cubes',
                        'url' => Backend::url('xeor/octocart/products'),
                        'attributes' => ['data-menu-item' => 'products'],
                        'permissions' => ['xeor.octocart.access_products'],
                    ],
                    'categories' => [
                        'label' => 'xeor.octocart::lang.categories.menu_label',
                        'icon' => 'icon-sitemap',
                        'url' => Backend::url('xeor/octocart/categories'),
                        'attributes' => ['data-menu-item' => 'categories'],
                        'permissions' => ['xeor.octocart.access_categories'],
                    ],
                ]

            ],
        ];
    }

    /**
     * Registers back-end settings for this plugin.
     *
     * @return array
     */
    public function registerSettings()
    {
        return [
            'settings' => [
                'label' => 'xeor.octocart::lang.settings.menu_label',
                'description' => 'xeor.octocart::lang.settings.menu_description',
                'category' => 'xeor.octocart::lang.plugin.name',
                'icon' => 'icon-cog',
                'class' => 'Xeor\OctoCart\Models\Settings',
                'order' => 200,
                'keywords'    => 'octocart settings'
            ],
            'shipping' => [
                'label' => 'xeor.octocart::lang.shipping.menu_label',
                'description' => 'xeor.octocart::lang.shipping.menu_description',
                'category' => 'xeor.octocart::lang.plugin.name',
                'icon' => 'icon-truck',
                'url' => Backend::url('xeor/octocart/shipping'),
                'order' => 300,
                'keywords' => 'octocart shipping methods'
            ],
            'payments' => [
                'label' => 'xeor.octocart::lang.payments.menu_label',
                'description' => 'xeor.octocart::lang.payments.menu_description',
                'category' => 'xeor.octocart::lang.plugin.name',
                'icon' => 'icon-credit-card',
                'url' => Backend::url('xeor/octocart/payments'),
                'order' => 400,
                'keywords' => 'octocart payments methods'
            ],
        ];
    }

    public function registerMarkupTags()
    {
        return [
            'filters' => [
                'price' => [$this, 'priceFilter'],
                'query' => function($text) {
                    $queryString = Request::server('QUERY_STRING');
                    if ($queryString) {
                        $queryString = '?' . $queryString;
                    }
                    return $text . $queryString;
                }
            ],
            'functions' => [
            ]
        ];
    }

    public function priceFilter($number)
    {
        return currency_format($number);
    }

    public function registerListColumnTypes()
    {
        return [
            'status' => function ($value) {
                switch ($value) {
                    case 'instock':
                        $value = Lang::get('xeor.octocart::lang.product.instock');
                        break;
                    case 'outofstock':
                        $value = Lang::get('xeor.octocart::lang.product.outofstock');
                        break;
                }
                return $value;
            },
        ];
    }


}
