<?php namespace Xeor\OctoCart\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateProductAttributesCode extends Migration
{

    public function up()
    {
        if (Schema::hasColumn('xeor_octocart_product_attributes', 'code')) {
            return;
        }

        Schema::table('xeor_octocart_product_attributes', function($table)
        {
            $table->text('code')->nullable()->after('product_id');
        });
    }

    public function down()
    {
        Schema::table('xeor_octocart_product_attributes', function($table)
        {
            $table->dropColumn('code');
        });
    }
}
