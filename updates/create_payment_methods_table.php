<?php namespace Xeor\OctoCart\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreatePaymentMethodsTable extends Migration
{

    public function up()
    {
        Schema::create('xeor_octocart_payment_methods', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->boolean('active')->default(0);
            $table->string('name');
            $table->string('code')->index();
            $table->integer('weight')->nullable()->index();
            $table->text('description')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });

        if (Schema::hasColumn('xeor_octocart_orders', 'payment_method_id') && Schema::hasColumn('xeor_octocart_orders', 'payment_method_name') && Schema::hasColumn('xeor_octocart_orders', 'payment_method_title')) {
            return;
        }

        Schema::table('xeor_octocart_orders', function($table)
        {
            $table->integer('payment_method_id')->nullable()->after('currency');
            $table->string('transaction_id')->nullable()->after('payment_method_id');
            $table->text('payment_data')->nullable()->after('transaction_id');
            $table->text('payment_response')->nullable()->after('payment_data');
            $table->timestamp('date_completed')->nullable()->after('updated_at');
            $table->timestamp('date_paid')->nullable()->after('transaction_id');

        });

        if (Schema::hasColumn('xeor_octocart_products', 'deleted_at')) {
            return;
        }

        //
        // Add additional fields to products
        //
        Schema::table('xeor_octocart_products', function($table)
        {
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('xeor_octocart_payment_methods');
    }

}
