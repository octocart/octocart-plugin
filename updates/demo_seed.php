<?php namespace DShoreman\Shop\Updates;

use Db;
use Seeder;
use Carbon\Carbon;
use Xeor\OctoCart\Models\ProductAttribute;
use Xeor\OctoCart\Models\Category;
use Xeor\OctoCart\Models\Product as ProductModel;

class DemoSeed extends Seeder {

    public function run()
    {

        $this->createProductAttributes([[
            1, 'Size', 'S'
        ],
        [
            1, 'Size', 'M'
        ],
        [
            1, 'Color', 'Green'
        ]]);

        $this->createCategories([[
            'Clothes', 'clothes ', NULL, 1, 6, 0,
            'Find your personal style!'
        ],
        [
            'T-Shirts', 't-shirts', 1, 2, 3, 1,
            'High quality T-Shirts & Hoodies by independent artists and designers from around the world.'
        ],
        [
            'Shoes', 'shoes', 1, 4, 5, 1,
            'Step into the season in style, with a new pair of women\'s shoes from our latest online collections.'
        ]]);

        $products = [
            [
                2, 'Long Sleeve Henley', 'long-sleeve-henley', '37.98',
                "<p>Product Description for Long Sleeve Henley - Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Sed eu dui. Phasellus eget orci volutpat sem accumsan condimentum. Etiam lobortis facilisis sem. Aliquam...</p>"
            ],
            [
                2, 'Polo', 'polo', '25.00',
                "<p>Product Description for Polo - Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Sed eu dui. Phasellus eget orci volutpat sem accumsan condimentum. Etiam lobortis facilisis sem. Aliquam...</p>"
            ],
            [
                3, 'Ready for the Beach', 'ready-for-the-beach', '6.99',
                "<p>Product Description for Ready for the Beach - Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Sed eu dui. Phasellus eget orci volutpat sem accumsan condimentum. Etiam lobortis facilisis sem. Aliquam...</p>"
            ],
            [
                3, 'Ready for the Court', 'ready-for-the-court', '8.99',
                "<p>Product Description for Ready for the Court - Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Sed eu dui. Phasellus eget orci volutpat sem accumsan condimentum. Etiam lobortis facilisis sem. Aliquam...</p>"
            ]
        ];

        for ($i = 1; $i <= 10; $i++) {
            $products[] = [
                2, 'Product ' . $i, 'product-' . $i, ($i * 10) . '.98',
                '<p>Product Description for Product-' . $i . ' - Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Sed eu dui. Phasellus eget orci volutpat sem accumsan condimentum. Etiam lobortis facilisis sem. Aliquam...</p>'
            ];
        }

        $this->createProducts($products);

    }

    public function createProductAttributes($attributes)
    {
        foreach ($attributes as $attribute)
        {
            $a = new ProductAttribute();
            $a->product_id = $attribute[0];
            $a->name = $attribute[1];
            $a->value = $attribute[2];
            $a->save();
        }
    }

    public function createCategories($categories)
    {
        foreach ($categories as $category)
        {
            $c = new Category;
            $c->title = $category[0];
            $c->slug = $category[1];
            $c->parent_id = $category[2];
            $c->nest_left = $category[3];
            $c->nest_right = $category[4];
            $c->nest_depth = $category[5];
            $c->description = $category[6];
            $c->save();
        }
    }

    public function createProducts($products)
    {
        foreach ($products as $product)
        {
            $p = new ProductModel;
            $p->title = $product[1];
            $p->slug = $product[2];
            $p->description = $product[4];
            $p->price = $product[3];
            $p->user_id = 1;
            $p->stock_status = 'instock';
            $p->published_at = Carbon::now();
            $p->published = true;

            if ($p->title == 'Product 1') {
                $p->type = 'variable';
            }
            else if ($p->title == 'Product 2' || $p->title == 'Product 3') {
                $p->type = 'product_variation';
            }
            else {
                $p->type = 'simple';
            }

            $p->save();

            Db::table('xeor_octocart_products_categories')->insert([
                ['product_id' => $p->id, 'category_id' => $product[0]]
            ]);
        }
    }

}
