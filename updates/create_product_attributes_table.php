<?php namespace Xeor\OctoCart\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateProductAttributesTable extends Migration
{

    public function up()
    {
        Schema::create('xeor_octocart_product_attributes', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('product_id')->unsigned()->nullable()->index();
            $table->string('name')->nullable();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('xeor_octocart_product_attributes');
    }

}
