<?php namespace Xeor\OctoCart\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateProductsVariations extends Migration
{

    public function up()
    {
        Schema::table('xeor_octocart_products', function($table)
        {
            $table->text('variations')->nullable()->after('cross_sells');
        });
    }

    public function down()
    {
        Schema::table('xeor_octocart_products', function($table)
        {
            $table->dropColumn('variations');
        });
    }
}
