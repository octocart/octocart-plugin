<?php namespace Xeor\OctoCart\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateCategoriesActive extends Migration
{

    public function up()
    {
        Schema::table('xeor_octocart_categories', function($table)
        {
            $table->boolean('active')->default(true)->after('description');
        });
    }

    public function down()
    {
        Schema::table('xeor_octocart_categories', function($table)
        {
            $table->dropColumn('active');
        });
    }
}
