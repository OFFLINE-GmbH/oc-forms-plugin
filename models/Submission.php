<?php namespace OFFLINE\Forms\Models;

use Model;

class Submission extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];

    public $table = 'offline_forms_submissions';

    public $rules = [
    ];

    public $belongsTo = [
        'form' => Form::class,
    ];

}
