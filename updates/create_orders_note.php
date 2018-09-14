<?php namespace Xeor\OctoCart\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateOrdersNote extends Migration
{

    public function up()
    {
        Schema::table('xeor_octocart_orders', function($table)
        {
            $table->text('note')->nullable()->after('shipping_info');
        });
    }

    public function down()
    {
        Schema::table('xeor_octocart_orders', function($table)
        {
            $table->dropColumn('note');
        });
    }
}
