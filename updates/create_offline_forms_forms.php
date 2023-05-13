<?php namespace OFFLINE\Forms\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateOfflineFormsForms extends Migration
{
    public function up()
    {
        Schema::create('offline_forms_forms', function($table)
        {
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->string('subject')->nullable();
            $table->string('slug')->nullable();
            $table->boolean('is_enabled')->default(1);
            $table->text('recipients')->nullable();
            $table->mediumText('fields')->nullable();
            $table->boolean('send_cc')->default(0);
            $table->boolean('spam_protection_enabled')->default(1);
            $table->integer('spam_limit_ip_15min')->default(3);
            $table->integer('spam_limit_global_1h')->default(10);
            $table->string('submit_button_label')->default('');
            $table->string('success_message');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('offline_forms_forms');
    }
}
