<?php

namespace OFFLINE\FormBuilder\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class AddSuccessScriptFieldToOfflineForms extends Migration
{
    public function up()
    {
        Schema::table('offline_forms_forms', function ($table) {
            $table->text('success_script')->after('send_cc')->nullable();
        });
    }

    public function down()
    {
        Schema::table('offline_forms_forms', function ($table) {
            $table->dropColumn('success_script');
        });
    }
}
