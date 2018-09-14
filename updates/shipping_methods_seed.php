<?php namespace DShoreman\Shop\Updates;

use Db;
use Seeder;
use Xeor\OctoCart\Models\ShippingMethod;

class ShippingMethodsSeed extends Seeder {

    public function run()
    {
        $methods = [
            [1, 'Free shipping', 'free_shipping', 0, 1, 'Free shipping is a special method which can be triggered with coupons and minimum spends.'],
            [1, 'Local pickup', 'local_pickup', 0, 2, 'Allow customers to pick up orders themselves. By default, when using local pickup store base taxes will apply regardless of customer address.'],
            [1, 'Flat rate', 'flat_rate', 10, 3, 'Lets you charge a fixed rate for shipping.'],
        ];

        $this->createShippingMethod($methods);
    }

    public function createShippingMethod($methods)
    {
        foreach ($methods as $method)
        {
            $shippingMetod = new ShippingMethod();
            $shippingMetod->active = $method[0];
            $shippingMetod->name = $method[1];
            $shippingMetod->code = $method[2];
            $shippingMetod->price = $method[3];
            $shippingMetod->weight = $method[4];
            $shippingMetod->description = $method[5];
            $shippingMetod->forceSave();
        }
    }


}
