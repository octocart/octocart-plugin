<?php namespace Xeor\OctoCart\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateOrdersPhone extends Migration
{

    public function up()
    {
        Schema::table('xeor_octocart_orders', function($table)
        {
            $table->string('phone')->nullable()->after('email');
        });

        // Fix billing & shipping info
        if ($orders = \Xeor\OctoCart\Models\Order::all()) {
            foreach ($orders as $order) {

                $billingInfo = $order->billing_info;
                $newBillingInfo = [];
                foreach ($billingInfo as $fieldName => $value) {
                    $newBillingInfo[] = ['name' => $fieldName, 'value' => $value];
                    if ($fieldName == 'phone') {
                        $order->phone = $value;
                    }
                }
                $order->billing_info = $newBillingInfo;

                $shippingInfo = $order->shipping_info;
                $newShippingInfo = [];
                foreach ($shippingInfo as $fieldName => $value) {
                    $newShippingInfo[] = ['name' => $fieldName, 'value' => $value];
                }
                $order->shipping_info = $newShippingInfo;

                $order->save();

            }
        }

    }

    public function down()
    {
        Schema::table('xeor_octocart_orders', function($table)
        {
            $table->dropColumn('phone');
        });
    }
}
