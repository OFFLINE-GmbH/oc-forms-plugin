<?php namespace OFFLINE\Forms\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateOfflineFormsSubmissions extends Migration
{
    public function up()
    {
        Schema::create('offline_forms_submissions', function($table)
        {
            $table->increments('id')->unsigned();
            $table->integer('form_id')->unsigned();
            $table->mediumText('data')->nullable();
            $table->string('ip_hash')->nullable();
            $table->string('port')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('offline_forms_submissions');
    }
}
