<?php namespace Xeor\OctoCart\Models;

use Backend\Models\ExportModel;
use ApplicationException;

/**
 * Product Attribute Export Model
 */
class ProductAttributeExport extends ExportModel
{
    public $table = 'xeor_octocart_product_attributes';


    public function exportData($columns, $sessionKey = null)
    {
        $result = self::make()
            ->get()
            ->toArray();

        return $result;
    }
}