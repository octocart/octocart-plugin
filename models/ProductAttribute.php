<?php namespace Xeor\OctoCart\Models;

use Model;

/**
 * Attribute Model
 */
class ProductAttribute extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'xeor_octocart_product_attributes';
    /*
     * Validation
     */
    public $rules = [
      'name' => 'required',
    ];


    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['name', 'value', 'code'];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'product' => ['Xeor\OctoCart\Models\Product']
    ];

    public function name()
    {
        return $this->name;
    }

    public function value()
    {
        return $this->value;
    }

    public function code()
    {
        return $this->code;
    }

}