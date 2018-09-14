<?php namespace Xeor\OctoCart\Models;

use Log;
use Str;
use File as FileHelper;
use Backend\Models\ImportModel;
use Backend\Models\User as AuthorModel;
use System\Models\File;
use ApplicationException;
use Exception;
use Carbon\Carbon;
use Config;

/**
 * Post Import Model
 */
class ProductImport extends ImportModel
{
    public $table = 'xeor_octocart_products';

    /**
     * Relations
     */
    public $attachOne = [
        'import_file' => \System\Models\File::class,
        'product_attributes_import_file' => \System\Models\File::class,
    ];

    /**
     * Validation rules
     */
    public $rules = [
        'title'   => 'required',
        'price' => 'required'
    ];

    protected $authorEmailCache = [];

    protected $categoryTitleCache = [];

    public function getDefaultAuthorOptions()
    {
        return AuthorModel::all()->lists('full_name', 'email');
    }

    public function getDefaultTypeOptions()
    {
        $product = new Product;
        return $product->getTypeOptions();
    }

    public function getDefaultStockStatusOptions()
    {
        $product = new Product;
        return $product->getStockStatusOptions();
    }

    public function getProductCategoriesOptions()
    {
        return Category::lists('title', 'id');
    }

    public function importData($results, $sessionKey = null)
    {
        $firstRow = reset($results);

        /*
         * Validation
         */
        if ($this->auto_create_categories && !array_key_exists('categories', $firstRow)) {
            throw new ApplicationException('Please specify a match for the Categories column.');
        }

        /*
         * Import
         */
        foreach ($results as $row => $data) {
            try {

                if (!$title = array_get($data, 'title')) {
                    $this->logSkipped($row, 'Missing product title');
                    continue;
                }

//                if (!$price = array_get($data, 'price')) {
//                    $this->logSkipped($row, 'Missing product price');
//                    continue;
//                }

                /*
                 * Find or create
                 */
                $product = Product::make();
                $product->published = true;
                $product->published_at = Carbon::now();

                if ($this->update_existing) {
                    $product = $this->findDuplicateProduct($data) ?: $product;
                }

                $productExists = $product->exists;

                /*
                 * Set attributes
                 */
                $except = ['id', 'images', 'categories', 'author_email', 'product_attributes_import_file'];

                foreach (array_except($data, $except) as $attribute => $value) {
                    $empty = null;
                    if ($attribute === 'price' || $attribute === 'sale_price') {
                        $empty = 0;
                        $value = (float)$value;
                    }
                    $product->{$attribute} = $value ?: $empty;
                }

                if ($author = $this->findAuthorFromEmail($data)) {
                    $product->user_id = $author->id;
                }

                if ($this->generate_slug) {
                    $product->slug = Str::slug($product->title);
                }

                if ($this->default_type) {
                    $product->type = $this->default_type;
                }

                if ($this->default_stock_status) {
                    $product->stock_status = $this->default_stock_status;
                }

                $product->forceSave();

                if ($images = $this->getImagesForProduct($data)) {
                    foreach ($images as $image) {
                        $pathToImage = base_path() . '/' . trim($image);
                        if (FileHelper::exists($pathToImage)) {

                            $file = new File;
                            $file->data = $pathToImage;
                            $file->is_public = true;
                            $file->save();

                            $product->images()->add($file);
                        }
                    }
                }

                if ($categoryIds = $this->getCategoryIdsForProduct($data)) {
                    $product->categories()->sync($categoryIds, false);
                }

                /*
                 * Log results
                 */
                if ($productExists) {
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

        try {
            if ($productAttributesImportFilePath = $this->getProductAttributesImportFilePath($sessionKey)) {
                $header = null;
                if (($handle = fopen($productAttributesImportFilePath,
                        'r')) !== false) {
                    while (($row = fgetcsv($handle, 1024, ';')) !== false) {
                        if (!$header) {
                            array_shift($row);
                            $header = $row;
                        } else {
                            $title = array_shift($row);
                            if ($productId = $this->findProductIdByTitle($title)) {
                                foreach ($row as $index => $cell) {
                                    $productAttribute = new ProductAttribute;
                                    $productAttribute->product_id = (int)$productId;
                                    $productAttribute->name = trim($header[$index]) ?: null;
                                    $productAttribute->value = trim($cell) ?: null;
                                    $productAttribute->save();
                                }
                            }
                        }
                    }
                    fclose($handle);
                }
            }
        } catch (Exception $ex) {
            Log::warning($ex->getMessage());
        }

    }

    protected function findAuthorFromEmail($data)
    {
        if (!$email = array_get($data, 'email', $this->default_author)) {
            return null;
        }

        if (isset($this->authorEmailCache[$email])) {
            return $this->authorEmailCache[$email];
        }

        $author = AuthorModel::where('email', $email)->first();
        return $this->authorEmailCache[$email] = $author;
    }

    protected function findDuplicateProduct($data)
    {
        if ($id = array_get($data, 'id')) {
            return Product::find($id);
        }

        $title = array_get($data, 'title');
        $product = Product::where('title', $title);

        if ($slug = array_get($data, 'slug')) {
            $product->orWhere('slug', $slug);
        }

        return $product->first();
    }

    protected function findProductIdByTitle($title)
    {
        $id = null;

        if (empty($title))
            return $id;

        if ($products = Product::where('title', $title)) {
            $product = $products->first();
            $id = $product ? $product->id : null;
        }
        return $id;
    }

    protected function getImagesForProduct($data)
    {
        $images = [];

        $string = array_get($data, 'images');

        if ($string) {
            $images = explode('|', $string);
        }

        return $images;
    }


    protected function getCategoryIdsForProduct($data)
    {
        $ids = [];

        if ($this->auto_create_categories) {
            $categoryTitles = $this->decodeArrayValue(array_get($data, 'categories'));

            foreach ($categoryTitles as $title) {
                if (!$title = trim($title)) {
                    continue;
                }

                if (isset($this->categoryTitleCache[$title])) {
                    $ids[] = $this->categoryTitleCache[$title];
                }
                else {
                    $newCategory = Category::firstOrCreate(['title' => $title]);
                    $ids[] = $this->categoryTitleCache[$title] = $newCategory->id;
                }
            }
        }
        elseif ($this->categories) {
            $ids = (array) $this->categories;
        }

        return $ids;
    }

    /**
     * @return string
     */
    public function getProductAttributesImportFilePath($sessionKey = null)
    {
        $file = $this
            ->product_attributes_import_file()
            ->withDeferred($sessionKey)
            ->orderBy('id', 'desc')
            ->first()
        ;

        if (!$file) {
            return null;
        }

        return $file->getLocalPath();
    }

}
