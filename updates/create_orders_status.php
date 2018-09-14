<?php namespace Xeor\OctoCart\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateOrdersStatus extends Migration
{

    public function up()
    {
        Schema::table('xeor_octocart_orders', function($table)
        {
            $table->string('status')->default('pending')->after('id');
        });
    }

    public function down()
    {
        Schema::table('xeor_octocart_orders', function($table)
        {
            $table->dropColumn('status');
        });
    }
}
