<?php namespace Xeor\OctoCart\Models;

use Event;
use Model;

/**
 * ShippingMethod Model
 */
class ShippingMethod extends Model
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
    public $table = 'xeor_octocart_shipping_methods';

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
            'free_shipping' => 'Free shipping',
            'local_pickup' => 'Local pickup',
            'flat_rate' => 'Flat rate',
        ];

        $methods = Event::fire('xeor.octocart.shippingMethods');
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