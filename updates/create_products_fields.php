<?php namespace Xeor\OctoCart\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateProductsFields extends Migration
{

    public function up()
    {
        Schema::table('xeor_octocart_products', function($table)
        {

            if (Schema::hasColumn('xeor_octocart_products', 'sale_price')) {
                return;
            }

            $table->dropColumn('active');

            $table->decimal('sale_price', 7, 2)->nullable()->after('price');
            $table->timestamp('published_at')->nullable()->after('published');
            $table->string('type')->default('simple')->after('slug');

            // Inventory
            $table->string('sku')->nullable()->after('type');
            $table->boolean('manage_stock')->default(false)->after('promote');
            $table->integer('quantity')->nullable()->unsigned()->after('manage_stock');
            $table->string('backorders')->nullable()->after('quantity');
            $table->string('stock_status')->nullable()->after('backorders');
            $table->boolean('sold_individually')->default(false)->after('stock_status');

            // Shipping
            $table->double('weight')->nullable()->after('sold_individually');
            $table->double('length')->nullable()->after('weight');
            $table->double('width')->nullable()->after('length');
            $table->double('height')->nullable()->after('width');

            // Linked products
            $table->longText('up_sells')->nullable()->after('height');
            $table->longText('cross_sells')->nullable()->after('up_sells');
        });
    }

    public function down()
    {
    }
}
