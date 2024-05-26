<?php

namespace OFFLINE\FormBuilder\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class AddUseCaptchaFieldToForms extends Migration
{
    public function up()
    {
        Schema::table('offline_forms_forms', function ($table) {
            $table->boolean('spam_use_captcha')->after('spam_limit_global_1h')->default(false);
        });
    }

    public function down()
    {
        Schema::table('offline_forms_forms', function ($table) {
            $table->dropColumn('spam_use_captcha');
        });
    }
}
