<?php namespace Xeor\OctoCart\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateCategoriesExcerpt extends Migration
{

    public function up()
    {
        Schema::table('xeor_octocart_categories', function($table)
        {
            $table->text('excerpt')->nullable()->after('external_id');
        });
    }

    public function down()
    {
        Schema::table('xeor_octocart_categories', function($table)
        {
            $table->dropColumn('excerpt');
        });
    }
}
