<?php namespace OFFLINE\Forms\Models;

use October\Rain\Argon\Argon;
use October\Rain\Database\ExpandoModel;
use October\Rain\Database\Relations\BelongsTo;

/**
 * @property string $ip_hash
 * @property string $port
 *
 * @property Form $form
 * @method BelongsTo form()
 */
class Submission extends ExpandoModel
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\SoftDelete;

    protected $expandoColumn = 'data';
    protected $expandoPassthru = [
        'form_id',
        'ip_hash',
        'port',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    public $table = 'offline_forms_submissions';

    public $rules = [
        'form_id' => 'required|exists:offline_forms_forms,id',
    ];

    public array $attributeNames = [];

    public $belongsTo = [
        'form' => Form::class,
    ];

    protected $fillable = [];

    protected $guarded = [
        'id',
        'form_id',
        'ip_hash',
        'port',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function beforeCreate()
    {
        $this->ip_hash = hash('sha256', request()?->ip());
        $this->port = request()?->getPort();

        if (!$this->created_at) {
            $this->created_at = Argon::now();
        }

        if (!$this->updated_at) {
            $this->updated_at = Argon::now();
        }
    }

    public function beforeValidate()
    {
        $this->rules = array_merge($this->rules, $this->form->getValidationRules());
        $this->attributeNames = array_merge($this->attributeNames, $this->form->getFieldNames());
    }

}
