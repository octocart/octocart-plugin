<?php namespace Xeor\OctoCart\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateProductsExternalId extends Migration
{

    public function up()
    {
        Schema::table('xeor_octocart_products', function($table)
        {
            $table->string('external_id')->nullable()->after('slug');
        });
    }

    public function down()
    {
        Schema::table('xeor_octocart_products', function($table)
        {
            $table->dropColumn('external_id');
        });
    }
}
