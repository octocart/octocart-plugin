<?php namespace Xeor\OctoCart\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateCategoriesTable extends Migration
{

    public function up()
    {
        Schema::create('xeor_octocart_categories', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('title')->index();
            $table->string('slug')->unique();
            $table->longText('description')->nullable();
            $table->integer('parent_id')->unsigned()->index()->nullable();
            $table->integer('nest_left')->nullable();
            $table->integer('nest_right')->nullable();
            $table->integer('nest_depth')->nullable();
            $table->timestamps();
        });

        Schema::create('xeor_octocart_products_categories', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('product_id')->unsigned();
            $table->integer('category_id')->unsigned();
            $table->primary(['product_id', 'category_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('xeor_octocart_categories');
        Schema::dropIfExists('xeor_octocart_products_categories');
    }

}
