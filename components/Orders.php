<?php namespace Xeor\OctoCart\Components;

use Auth;
use Cms\Classes\ComponentBase;
use Xeor\OctoCart\Models\Settings;
use Xeor\OctoCart\Models\Order as OrderModel;

class Orders extends ComponentBase
{

    /**
     * A collection of orders to display
     * @var Collection
     */
    public $orders;

    /**
     * Message to display when there are no products.
     * @var string
     */
    public $noOrdersMessage;

    /**
     * Reference to the page name for linking to order.
     * @var string
     */
    public $orderDisplayPage;

    public function componentDetails()
    {
        return [
            'name'        => 'xeor.octocart::lang.orders.name',
            'description' => 'xeor.octocart::lang.orders.description'
        ];
    }

    public function defineProperties()
    {
        return [
            'noOrdersMessage' => [
                'title'        => 'xeor.octocart::lang.orders.no_orders',
                'description'  => 'xeor.octocart::lang.orders.no_orders_description',
                'type'         => 'string',
                'default'      => 'No orders found',
                'showExternalParam' => false
            ]
        ];
    }

    public function onRun()
    {
        $this->prepareVars();
        $this->orders = $this->page['orders'] = $this->loadOrders();
    }

    protected function prepareVars()
    {
        $this->noOrdersMessage = $this->page['noOrdersMessage'] = $this->property('noOrdersMessage');

        /*
         * Page links
         */
        $this->orderDisplayPage = $this->page['orderDisplayPage'] = Settings::get('order_display_page', 'order');
    }

    protected function loadOrders()
    {
        $user = Auth::getUser();
        if (!isset($user)) {
            return array();
        }
        else {
            $orders = OrderModel::all()->where('user_id', $user->id);
            /*
             * Add a "url" helper attribute for linking to each category
             */
            return $orders->each(function($order) {
                $order->setUrl($this->orderDisplayPage, $this->controller);
            });
        }
    }

}