<?php namespace Xeor\OctoCart\Models;

use Lang;
use Event;
use Model;

/**
 * PaymentMethod Model
 */
class PaymentMethod extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\SoftDelete;

    /**
     * The attributes that should be mutated to dates.
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'xeor_octocart_payment_methods';

    /*
     * Validation
     */
    public $rules = [
        'name' => 'required',
        'code' => 'required',
    ];

    public $belongsTo = [
        'order' => ['Xeor\OctoCart\Models\Order']
    ];

    public function getCodeOptions($value, $formData)
    {
        $options = [
            'cod' => 'Cash on delivery',
            'cop' => 'Cash on pickup',
        ];

        $methods = Event::fire('xeor.octocart.paymentMethods');
        if (is_array($methods) && !empty($methods)) {
            foreach ($methods as $method) {
                if (!is_array($method)) {
                    continue;
                }

                foreach ($method as $code => $name) {
                    $options[$code] = $name;
                }
            }
        }

        return $options;
    }
}