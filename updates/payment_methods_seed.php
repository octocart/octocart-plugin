<?php namespace DShoreman\Shop\Updates;

use Db;
use Seeder;
use Xeor\OctoCart\Models\PaymentMethod;

class PaymentMethodsSeed extends Seeder {

    public function run()
    {
        $methods = [
            [1, 'Cash on delivery', 'cod', 1, 'Pay with cash upon delivery.'],
            [1, 'Cash on pickup', 'cop', 2, 'Pay with cash on pickup.'],
        ];

        $this->createPaymentMethod($methods);
    }

    public function createPaymentMethod($methods)
    {
        foreach ($methods as $method)
        {
            $shippingMetod = new PaymentMethod();
            $shippingMetod->active = $method[0];
            $shippingMetod->name = $method[1];
            $shippingMetod->code = $method[2];
            $shippingMetod->weight = $method[3];
            $shippingMetod->description = $method[4];
            $shippingMetod->forceSave();
        }
    }


}
