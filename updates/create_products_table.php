<?php namespace Xeor\OctoCart\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateProductsTable extends Migration
{

    public function up()
    {
        Schema::create('xeor_octocart_products', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('user_id')->unsigned()->nullable()->index();
            $table->string('title')->index()->nullable();
            $table->string('slug')->index()->unique();
            $table->text('excerpt')->nullable();
            $table->longText('description')->nullable();
            $table->decimal('price', 7, 2)->default(0);
            $table->boolean('published')->default(true);
            $table->boolean('promote')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('xeor_octocart_products');
    }

}
