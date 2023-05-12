<?php namespace OFFLINE\Forms\Models;

use Model;
use October\Rain\Database\Relations\HasMany;

/**
 * @property string $name
 * @property array $fields
 * @property boolean $spam_protection_enabled
 * @property integer $spam_limit_ip_15min
 * @property integer $spam_limit_global_1h
 *
 * @property \October\Rain\Database\Collection<Submission> $submissions
 *
 * @method HasMany submissions()
 */
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

    public $jsonable = ['recipients', 'fields'];

    public $casts = [
        'spam_protection_enabled' => 'boolean',
        'spam_limit_ip_15min' => 'integer',
        'spam_limit_global_1h' => 'integer',
    ];

    public function beforeSave()
    {
        $this->setFieldNames();
    }

    public static function getFormOptions()
    {
        return self::orderBy('name')->pluck('name', 'id')->toArray();
    }

    /**
     * Set the name of each field that has no name set.
     */
    protected function setFieldNames(): void
    {
        $names = [];

        $this->fields = collect($this->fields)
            ->map(function ($field) use (&$names) {
                if (!$field['name']) {
                    $field['name'] = str_slug(str_replace('-', '_', $field['label']), '_');
                }

                // Ensure names are unique.
                while (in_array($field['name'], $names, true)) {
                    $field['name'] .= '_' . str_random(5);
                }

                $names[] = $field['name'];

                return $field;
            })
            ->toArray();
    }

    /**
     * Return validation rules for this form.
     */
    public function getValidationRules(): array
    {
        return collect($this->fields)
            ->mapWithKeys(function (array $field) {
                $rules = [];

                if (array_get($field, 'is_required')) {
                    $rules[] = 'required';
                }

                if (array_get($field, 'type') === 'email') {
                    $rules[] = 'email';
                }

                return count($rules) > 0
                    ? [$field['name'] => implode('|', $rules)]
                    : [];
            })
            ->filter()
            ->toArray();
    }

    /**
     * Return field names for this form.
     */
    public function getFieldNames(): array
    {
        return collect($this->fields)
            ->mapWithKeys(fn(array $field) => [$field['name'] => $field['label']])
            ->toArray();
    }
}