<?php namespace OFFLINE\Forms\Models;

use Model;

class Form extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\Sluggable;

    public $table = 'offline_forms_forms';

    public $rules = [
        'name' => 'required',
        'recipients.*.email' => 'required',
    ];

    public $customMessages = [
        'recipients.*.email' => 'offline.forms::lang.recipients_email_validation',
    ];

    public $hasMany = ['submissions' => Submission::class];

    public $slugs = ['slug' => 'name'];

    public $jsonable = ['recipients'];

    public $casts = [
        'spam_protection_enabled' => 'boolean',
        'spam_limit_ip_15min' => 'integer',
        'spam_limit_global_1h' => 'integer',
    ];
}
