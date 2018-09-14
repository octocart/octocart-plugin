<?php namespace Xeor\OctoCart\Components;

use Db;
use App;
use Request;
use Carbon\Carbon;
use Cms\Classes\ComponentBase;
use Xeor\OctoCart\Models\Category;
use Xeor\OctoCart\Models\Settings;

class Categories extends ComponentBase
{
    /**
     * @var Collection A collection of categories to display
     */
    public $categories;

    /**
     * @var string Reference to the page name for linking to categories.
     */
    public $categoryPage;

    /**
     * @var string Reference to the current category slug.
     */
    public $currentCategorySlug;

    public function componentDetails()
    {
        return [
            'name'        => 'xeor.octocart::lang.categories.name',
            'description' => 'xeor.octocart::lang.categories.description'
        ];
    }

    public function defineProperties()
    {
        return [
            'slug' => [
                'title'       => 'xeor.octocart::lang.categories.category_slug',
                'description' => 'xeor.octocart::lang.categories.category_slug_description',
                'default'     => '{{ :slug }}',
                'type'        => 'string'
            ],
            'displayEmpty' => [
                'title'       => 'xeor.octocart::lang.categories.category_display_empty',
                'description' => 'xeor.octocart::lang.categories.category_display_empty_description',
                'type'        => 'checkbox',
                'default'     => 0
            ],
        ];
    }

    public function onRun()
    {
        $this->prepareVars();
        $this->categories = $this->page['categories'] = $this->loadCategories();
    }

    protected function prepareVars()
    {
        $this->currentCategorySlug = $this->page['currentCategorySlug'] = $this->property('slug');

        /*
         * Page links
         */
        $this->categoryPage = $this->page['categoryPage'] = Settings::get('category_page', 'category');
    }

    protected function loadCategories()
    {
        $categories = Category::where('active', 1)->orderBy('title');

        if (!$this->property('displayEmpty')) {
            $categories->whereExists(function($query) {
                $query->select(Db::raw(1))
                    ->from('xeor_octocart_products_categories')
                    ->join('xeor_octocart_products', 'xeor_octocart_products.id', '=', 'xeor_octocart_products_categories.product_id')
                    ->whereNotNull('xeor_octocart_products.published')
                    ->where('xeor_octocart_products.published', '=', 1)
                    ->whereNotNull('xeor_octocart_products.published_at')
                    ->where('xeor_octocart_products.published_at', '<', Carbon::now())
                    ->whereRaw('xeor_octocart_categories.id = xeor_octocart_products_categories.category_id');
            });
        }

        $categories = $categories->getNested();

        /*
         * Add a "url" helper attribute for linking to each category
         */
        return $this->linkCategories($categories);
    }

    protected function linkCategories($categories)
    {
        return $categories->each(function($category) {
            $category->setUrl($this->categoryPage, $this->controller);

            if ($category->children) {
                $this->linkCategories($category->children);
            }
        });
    }
}