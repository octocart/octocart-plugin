<?php namespace Xeor\OctoCart\Models;

use Backend\Models\ImportModel;
use ApplicationException;
use Exception;

/**
 * Attribute Model
 */
class ProductAttributeImport extends ImportModel
{

    public $table = 'xeor_octocart_product_attributes';

    /**
     * Validation rules
     */
    public $rules = [
        'name'   => 'required',
    ];


    public function importData($results, $sessionKey = null)
    {

        /*
         * Import
         */
        foreach ($results as $row => $data) {
            try {

                if (!$name = array_get($data, 'name')) {
                    $this->logSkipped($row, 'Missing product attribute name');
                    continue;
                }

                /*
                 * Find or create
                 */
                $productAttribute = ProductAttribute::make();

                if ($this->update_existing) {
                    $productAttribute = $this->findDuplicateProductAttribute($data) ?: $productAttribute;
                }

                $productAttributeExists = $productAttribute->exists;

                $productId = array_get($data, 'product_id');
                if ($this->use_product_title) {
                    $productTitle = $productId;
                    $product = Product::where('title', $productTitle)->first();
                    $productId = $product->id;
                }

                $productAttribute->product_id = $productId;

                /*
                 * Set attributes
                 */
                $except = ['id', 'product_id'];

                foreach (array_except($data, $except) as $attribute => $value) {
                    $productAttribute->{$attribute} = $value ?: null;
                }

                $productAttribute->forceSave();

                /*
                 * Log results
                 */
                if ($productAttributeExists) {
                    $this->logUpdated();
                }
                else {
                    $this->logCreated();
                }

            }
            catch (Exception $ex) {
                $this->logError($row, $ex->getMessage());
            }
        }
    }

    protected function findDuplicateProductAttribute($data)
    {
        if ($id = array_get($data, 'id')) {
            return ProductAttribute::find($id);
        }

        $productAttribute = null;

        if ($name = array_get($data, 'name')) {
            $productAttribute = ProductAttribute::where('name', $name);
        }
        elseif ($code = array_get($data, 'code')) {
            $productAttribute = ProductAttribute::where('code', $code);
        }

        if (!is_null($productAttribute)) {
            $value = array_get($data, 'value');
            $productAttribute = $productAttribute->where('value', $value);
            $productAttribute = $productAttribute->first();
        }

        return $productAttribute;
    }

}