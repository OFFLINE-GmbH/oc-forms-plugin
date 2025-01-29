<?php

namespace OFFLINE\FormBuilder\Updates;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use October\Rain\Database\Updates\Migration;
use Schema;

class AddMailSentAtFieldToSubmissions extends Migration
{
    public function up()
    {
        Schema::table('offline_forms_submissions', function ($table) {
            $table->datetime('mail_sent_at')->after('updated_at')->nullable();
        });

        // Give past submissions any sent date.
        DB::table('offline_forms_submissions')->update(['mail_sent_at' => Carbon::create(1970)]);
    }

    public function down()
    {
        Schema::table('offline_forms_submissions', function ($table) {
            $table->dropColumn('mail_sent_at');
        });
    }
}
