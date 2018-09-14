<?php namespace Xeor\OctoCart\Models;

use Auth;
use Lang;
use Event;
use Model;

/**
 * Order Model
 */
class Order extends Model
{
    use \October\Rain\Database\Traits\Encryptable;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'xeor_octocart_orders';

    /**
     * The attributes that should be mutated to dates.
     * @var array
     */
    protected $dates = ['date_completed', 'date_paid'];

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    protected $jsonable = ['billing_info', 'shipping_info'];

    /**
     * The attributes that should be encrypted for arrays.
     *
     * @var array
     */
    public $encryptable = ['payment_data', 'payment_response'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['payment_data'];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [
        'user' => ['RainLab\User\Models\User'],
        'payment_method' => ['Xeor\OctoCart\Models\PaymentMethod', 'order' => 'weight asc'],
        'shipping_method'  => ['Xeor\OctoCart\Models\ShippingMethod', 'order' => 'weight asc'],
    ];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

    public function beforeSave()
    {
        $user = Auth::getUser();
        if (!isset($user)) {
            $this->user_id = 0;
        }
        else {
            $this->user_id = $user['attributes']['id'];
        }
    }

    public function afterUpdate()
    {
        Event::fire('xeor.octocart.afterOrderUpdate', [$this]);
    }

    public function getStatusOptions($keyValue = null)
    {

        return [
            'pending' => Lang::get('xeor.octocart::lang.status.pending'),
            'processing' => Lang::get('xeor.octocart::lang.status.processing'),
            'on-hold' => Lang::get('xeor.octocart::lang.status.on-hold'),
            'paid' => Lang::get('xeor.octocart::lang.status.paid'),
            'completed' => Lang::get('xeor.octocart::lang.status.completed'),
            'cancelled' =>Lang::get('xeor.octocart::lang.status.cancelled'),
            'refunded' =>Lang::get('xeor.octocart::lang.status.refunded'),
            'failed' => Lang::get('xeor.octocart::lang.status.failed'),
        ];

    }

    /**
     * Sets the "url" attribute with a URL to this object
     * @param string $pageName
     * @param Cms\Classes\Controller $controller
     */
    public function setUrl($pageName, $controller)
    {
        $params = [
            'id' => $this->id,
            'slug' => $this->slug,
        ];
        return $this->url = $controller->pageUrl($pageName, $params);
    }

    public function getTotal()
    {
        return (float)$this->total + (float)$this->shipping_total;
    }

    public function getSubTotal()
    {
        return $this->total;
    }

    public function getShippingTotal()
    {
        return is_null($this->shipping_total) ? 0 : $this->shipping_total;
    }

    public function getShippingTax()
    {
        return is_null($this->shipping_tax) ? 0 : $this->shipping_tax;
    }

    public function getBillingPhone()
    {
        return $this->phone;
    }

    public function getBillingEmail()
    {
        return $this->email;
    }

    public function getShippingMethodName()
    {
        $shippingMethod = $this->shipping_metod;
        return $shippingMethod ? $shippingMethod->name : '';
    }

    public function getItems()
    {
        return json_decode($this->items, true);
    }
}