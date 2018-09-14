<?php namespace Xeor\OctoCart\Models;

use Str;
use Model;
use URL;
use Cms\Classes\Page as CmsPage;
use Cms\Classes\Theme;

/**
 * Category Model
 */
class Category extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\NestedTree;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'xeor_octocart_categories';

    /*
     * Validation
     */
    public $rules = [
        'title' => 'required',
        'slug' => 'required|between:3,64|unique:xeor_octocart_categories',
    ];

    /**
     * @var array Guarded fields
     */
    protected $guarded = [];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [];
    public $belongsToMany = [
        'products' => ['Xeor\OctoCart\Models\Product',
            'table' => 'xeor_octocart_products_categories',
            'order' => 'published_at desc',
            'scope' => 'isPublished'
        ]
    ];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [
        'image' => ['System\Models\File', 'delete' => true],
    ];
    public $attachMany = [
        'images' => ['System\Models\File', 'order' => 'sort_order', 'delete' => true],
    ];

    public function beforeValidate()
    {
        // Generate a URL slug for this model
        if (!$this->exists && !$this->slug)
            $this->slug = Str::slug($this->title);
    }

    public function afterDelete()
    {
        $this->products()->detach();
    }

    public function getProductCountAttribute()
    {
        return $this->products()->count();
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

    /**
     * Handler for the pages.menuitem.getTypeInfo event.
     * Returns a menu item type information. The type information is returned as array
     * with the following elements:
     * - references - a list of the item type reference options. The options are returned in the
     *   ["key"] => "title" format for options that don't have sub-options, and in the format
     *   ["key"] => ["title"=>"Option title", "items"=>[...]] for options that have sub-options. Optional,
     *   required only if the menu item type requires references.
     * - nesting - Boolean value indicating whether the item type supports nested items. Optional,
     *   false if omitted.
     * - dynamicItems - Boolean value indicating whether the item type could generate new menu items.
     *   Optional, false if omitted.
     * - cmsPages - a list of CMS pages (objects of the Cms\Classes\Page class), if the item type requires a CMS page reference to
     *   resolve the item URL.
     * @param string $type Specifies the menu item type
     * @return array Returns an array
     */
    public static function getMenuTypeInfo($type)
    {
        $result = [];

        if ($type == 'octocart-category') {
            $result = [
                'references'   => self::listSubCategoryOptions(),
                'nesting'      => true,
                'dynamicItems' => true
            ];
        }

        if ($type == 'all-octocart-categories') {
            $result = [
                'dynamicItems' => true
            ];
        }

        if ($result) {
            $theme = Theme::getActiveTheme();

            $pages = CmsPage::listInTheme($theme, true);
            $cmsPages = [];
            foreach ($pages as $page) {
                if (!$page->hasComponent('products')) {
                    continue;
                }

                /*
                 * Component must use a category filter with a routing parameter
                 * eg: categoryFilter = "{{ :somevalue }}"
                 */
                $properties = $page->getComponentProperties('products');
                if (!isset($properties['categoryFilter']) || !preg_match('/{{\s*:/', $properties['categoryFilter'])) {
                    continue;
                }

                $cmsPages[] = $page;
            }

            $result['cmsPages'] = $cmsPages;
        }

        return $result;
    }

    protected static function listSubCategoryOptions()
    {
        $category = self::make()->getAllRoot();

        $iterator = function($categories) use (&$iterator) {
            $result = [];

            foreach ($categories as $category) {
                if (!$category->children) {
                    $result[$category->id] = $category->name;
                }
                else {
                    $result[$category->id] = [
                        'title' => $category->name,
                        'items' => $iterator($category->children)
                    ];
                }
            }

            return $result;
        };

        return $iterator($category);
    }

    /**
     * Handler for the pages.menuitem.resolveItem event.
     * Returns information about a menu item. The result is an array
     * with the following keys:
     * - url - the menu item URL. Not required for menu item types that return all available records.
     *   The URL should be returned relative to the website root and include the subdirectory, if any.
     *   Use the URL::to() helper to generate the URLs.
     * - isActive - determines whether the menu item is active. Not required for menu item types that
     *   return all available records.
     * - items - an array of arrays with the same keys (url, isActive, items) + the title key.
     *   The items array should be added only if the $item's $nesting property value is TRUE.
     * @param \RainLab\Pages\Classes\MenuItem $item Specifies the menu item.
     * @param \Cms\Classes\Theme $theme Specifies the current theme.
     * @param string $url Specifies the current page URL, normalized, in lower case
     * The URL is specified relative to the website root, it includes the subdirectory name, if any.
     * @return mixed Returns an array. Returns null if the item cannot be resolved.
     */
    public static function resolveMenuItem($item, $url, $theme)
    {
        $result = null;

        if ($item->type == 'octocart-category') {
            if (!$item->reference || !$item->cmsPage)
                return;

            $category = self::find($item->reference);
            if (!$category)
                return;

            $pageUrl = self::getCategoryPageUrl($item->cmsPage, $category, $theme);
            if (!$pageUrl)
                return;

            $pageUrl = URL::to($pageUrl);

            $result = [];
            $result['url'] = $pageUrl;
            $result['isActive'] = $pageUrl == $url;
            $result['mtime'] = $category->updated_at;

            if ($item->nesting) {
                $categories = $category->getAllRoot();
                $iterator = function($categories) use (&$iterator, &$item, &$theme, $url) {
                    $branch = [];

                    foreach ($categories as $category) {

                        $branchItem = [];
                        $branchItem['url'] = self::getCategoryPageUrl($item->cmsPage, $category, $theme);
                        $branchItem['isActive'] = $branchItem['url'] == $url;
                        $branchItem['title'] = $category->name;
                        $branchItem['mtime'] = $category->updated_at;

                        if ($category->children) {
                            $branchItem['items'] = $iterator($category->children);
                        }

                        $branch[] = $branchItem;
                    }

                    return $branch;
                };

                $result['items'] = $iterator($categories);
            }
        }
        elseif ($item->type == 'all-octocart-categories') {
            $result = [
                'items' => []
            ];

            $categories = self::orderBy('name')->get();
            foreach ($categories as $category) {
                $categoryItem = [
                    'title' => $category->name,
                    'url'   => self::getCategoryPageUrl($item->cmsPage, $category, $theme),
                    'mtime' => $category->updated_at,
                ];

                $categoryItem['isActive'] = $categoryItem['url'] == $url;

                $result['items'][] = $categoryItem;
            }
        }

        return $result;
    }

    /**
     * Returns URL of a category page.
     */
    protected static function getCategoryPageUrl($pageCode, $category, $theme)
    {
        $page = CmsPage::loadCached($theme, $pageCode);
        if (!$page) return;

        $properties = $page->getComponentProperties('products');
        if (!isset($properties['categoryFilter'])) {
            return;
        }

        /*
         * Extract the routing parameter name from the category filter
         * eg: {{ :someRouteParam }}
         */
        if (!preg_match('/^\{\{([^\}]+)\}\}$/', $properties['categoryFilter'], $matches)) {
            return;
        }

        $paramName = substr(trim($matches[1]), 1);
        $url = CmsPage::url($page->getBaseFileName(), [$paramName => $category->slug]);

        return $url;
    }

}