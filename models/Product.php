<?php namespace Xeor\OctoCart\Models;

use Db;
use App;
use Html;
use Log;
use Str;
use Lang;
use Input;
use Model;
use Markdown;
use Carbon\Carbon;
use Backend\Models\User;
use ValidationException;

/**
 * Product Model
 */
class Product extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\SoftDelete;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'xeor_octocart_products';

    /*
     * Validation
     */
    public $rules = [
        'title' => 'required',
        'slug' => ['required', 'regex:/^[a-z0-9\/\:_\-\*\[\]\+\?\|]*$/i', 'unique:xeor_octocart_products'],
        'price' => 'required',
        'type' => 'required',
        'stock_status' => 'required',
    ];

    /**
     * The attributes that should be mutated to dates.
     * @var array
     */
    protected $dates = ['published_at', 'deleted_at'];

    /**
     * The attributes on which the product list can be ordered
     * @var array
     */
    public static $allowedSortingOptions = [
        'title-asc' => 'Title (ascending)',
        'title-desc' => 'Title (descending)',
        'created_at-asc' => 'Created (ascending)',
        'created_at-desc' => 'Created (descending)',
        'updated_at-asc' => 'Updated (ascending)',
        'updated_at-desc' => 'Updated (descending)',
        'random' => 'Random',
        'url' => 'From URL',
    ];

    public static $allowedDirectionOptions = [
        'asc',
        'desc',
    ];

    /**
     * @var array Guarded fields
     */
    //protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    //protected $fillable = [];

    /**
     * @var array List of attribute names which are json encoded and decoded from the database.
     */
    //protected $jsonable = ['variations'];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [
        'productAttributes' => [
            'Xeor\OctoCart\Models\ProductAttribute',
            'order' => 'name', //TODO Custom order
            'delete' => true
        ],
    ];
    public $belongsTo = [
        'user' => ['Backend\Models\User']
    ];
    public $belongsToMany = [
        'categories' => [
            'Xeor\OctoCart\Models\Category',
            'table' => 'xeor_octocart_products_categories',
            'order' => 'title',
            'label' => 'title'
        ]
    ];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [
        'images' => ['System\Models\File', 'order' => 'sort_order', 'delete' => true],
    ];

    public function afterDelete()
    {
        $this->images()->delete();
    }

    public function getUpSellsOptions($keyValue = null)
    {
        $options = [];
        if ($products = Db::table('xeor_octocart_products')->lists('title', 'id')) {
            foreach ($products as $productId => $productTitle) {
                $options[$productId] = '#' . $productId . ' – ' . $productTitle;
            }
        }
        return $options;
    }

    public function getCrossSellsOptions($keyValue = null)
    {
        $options = [];
        if ($products = Db::table('xeor_octocart_products')->lists('title', 'id')) {
            foreach ($products as $productId => $productTitle) {
                $options[$productId] = '#' . $productId . ' – ' . $productTitle;
            }
        }
        return $options;
    }

    public function getTypeOptions($keyValue = null)
    {
        return [
            'simple' => Lang::get('xeor.octocart::lang.product.simple'),
            'variable' => Lang::get('xeor.octocart::lang.product.variable'),
            'product_variation' => Lang::get('xeor.octocart::lang.product.product_variation')
        ];
    }

    public function getBackOrdersOptions($keyValue = null)
    {
        return [
            'no' => Lang::get('xeor.octocart::lang.product.backorders_no'),
            'notify' => Lang::get('xeor.octocart::lang.product.backorders_notify'),
            'yes' => Lang::get('xeor.octocart::lang.product.backorders_yes'),
        ];
    }

    public function getStockStatusOptions($keyValue = null)
    {
        return [
            'instock' => Lang::get('xeor.octocart::lang.product.instock'),
            'outofstock' => Lang::get('xeor.octocart::lang.product.outofstock'),
        ];
    }

    public function getVariationsAttribute($value)
    {
        $variations = [];
        if ($value) {
            $ids = json_decode($value, true);
            foreach ($ids as $id) {
                $variations[] = self::find($id);
            }
        }
        return $variations;
    }

    public function setVariationsAttribute($value)
    {
        if (is_array($value)) {
            $variations = array_map(function ($array) {
                return end($array);
            }, array_column($value, 'id'));
        }
        $this->attributes['variations'] = json_encode($variations);
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

        if (array_key_exists('categories', $this->getRelations())) {
            $params['category'] = $this->categories->count() ? $this->categories->first()->slug : null;
        }

        return $this->url = $controller->pageUrl($pageName, $params);
    }

    /**
     * Used to test if a certain user has permission to edit product,
     * returns TRUE if the user is the owner or has products access.
     * @param User $user
     * @return bool
     */
    public function canEdit(User $user)
    {
        return ($this->user_id == $user->id) || $user->hasAnyAccess(['xeor.octocart.access_products']);
    }

    //
    // Scopes
    //

    public function scopeIsPublished($query)
    {
        return $query
            ->whereNotNull('published')
            ->where('published', true)
            ->where('type', '<>', 'product_variation')
            ->whereNotNull('published_at')
            ->where('published_at', '<', Carbon::now())
            ;
    }

    public function scopeIsPromoted($query)
    {
        return $query
            ->whereNotNull('promote')
            ->where('promote', true)
            ;
    }

    /**
     * Lists products for the front end
     * @param  array $options Display options
     * @return self
     */
    public function scopeListFrontEnd($query, $options)
    {
        /*
         * Default options
         */
        extract(array_merge([
            'page'       => 1,
            'perPage'    => 30,
            'sort'       => 'created_at',
            'categories' => null,
            'category'   => null,
            'search'     => '',
            'published'  => true,
            'promote'    => false,
        ], $options));

        $searchableFields = ['title', 'slug', 'description'];

        if ($published) {
            $query->isPublished();
        }

        if ($promote) {
            $query->isPromoted();
        }

        /*
         * Sorting
         */
        if (!is_array($sort)) {
            $sort = [$sort];
        }

        foreach ($sort as $_sort) {

            if (in_array($_sort, array_keys(self::$allowedSortingOptions))) {

                $parts = explode('-', $_sort);
                if (count($parts) < 2) {
                    array_push($parts, 'desc');
                }
                list($sortField, $sortDirection) = $parts;


                if ($sortField === 'random') {
                    $sortField = DB::raw('RAND()');
                }
                elseif ($sortField === 'url') {

                    $allowedSortingParams = ['created_at', 'updated_at', 'price'];
                    $sortingParam = Input::get('orderby');
                    $parts = explode('-', $sortingParam);
                    if (count($parts) < 2) {
                        array_push($parts, 'desc');
                    }
                    list($sortField, $sortDirection) = $parts;
                    if (!in_array($sortField, $allowedSortingParams)) {
                        $sortField = $allowedSortingParams[0];
                    }

                }

                if (!in_array($sortDirection, self::$allowedDirectionOptions)) {
                    $sortDirection = self::$allowedDirectionOptions[0];
                }

                $query->orderBy($sortField, $sortDirection);
            }
        }

        /*
         * Search
         */
        $search = trim($search);
        if (strlen($search)) {
            $query->searchWhere($search, $searchableFields);
        }

        /*
         * Categories
         */
        if ($categories !== null) {
            if (!is_array($categories)) $categories = [$categories];
            $query->whereHas('categories', function($q) use ($categories) {
                $q->whereIn('id', $categories);
            });
        }

        /*
         * Category, including children
         */
        if ($category !== null) {
            $category = Category::find($category);

            $categories = $category->getAllChildrenAndSelf()->lists('id');
            $query->whereHas('categories', function($q) use ($categories) {
                $q->whereIn('id', $categories);
            });
        }

        return $query->paginate((int)$perPage, $page);
    }

    /**
     * Allows filtering for specifc categories
     * @param  Illuminate\Query\Builder  $query      QueryBuilder
     * @param  array                     $categories List of category ids
     * @return Illuminate\Query\Builder              QueryBuilder
     */
    public function scopeFilterCategories($query, $categories)
    {
        return $query->whereHas('categories', function($q) use ($categories) {
            $q->whereIn('id', $categories);
        });
    }

    public function beforeSave()
    {
        if ($this->published && !$this->published_at) {
            $this->published_at = Carbon::now();
        }
    }

    public function getPrice()
    {
        return $this->sale_price > 0 ? $this->sale_price : $this->price;
    }
}