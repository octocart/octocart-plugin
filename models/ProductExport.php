<?php namespace Xeor\OctoCart\Models;

use Backend\Models\ExportModel;
use ApplicationException;

/**
 * Product Export Model
 */
class ProductExport extends ExportModel
{
    public $table = 'xeor_octocart_products';

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'user' => [
            'Backend\Models\User',
            'key' => 'user_id'
        ]
    ];

    public $belongsToMany = [
        'product_categories' => [
            'Xeor\OctoCart\Models\Category',
            'table' => 'xeor_octocart_products_categories',
            'key' => 'product_id',
            'otherKey' => 'category_id'
        ]
    ];

    /**
     * The accessors to append to the model's array form.
     * @var array
     */
    protected $appends = [
        'author_email',
        'categories'
    ];

    public function exportData($columns, $sessionKey = null)
    {
        $result = self::make()
            ->with([
                'user',
                'product_categories'
            ])
            ->get()
            ->toArray()
        ;

        return $result;
    }

    public function getAuthorEmailAttribute()
    {
        if (!$this->user) {
            return '';
        }

        return $this->user->email;
    }

    public function getCategoriesAttribute()
    {
        if (!$this->product_categories) {
            return '';
        }

        return $this->encodeArrayValue($this->product_categories->lists('title'));
    }
}
