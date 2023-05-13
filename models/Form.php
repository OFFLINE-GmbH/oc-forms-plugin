<?php namespace OFFLINE\Forms\Models;

use Model;
use October\Rain\Database\Relations\HasMany;
use RuntimeException;
use ValidationException;

/**
 * @property string $name
 * @property string $success_message
 * @property array $fields
 * @property boolean $send_cc
 * @property array{'email': string, 'name': string} $recipients
 * @property boolean $spam_protection_enabled
 * @property integer $spam_limit_ip_15min
 * @property integer $spam_limit_global_1h
 * @property string $mail_subject
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
        'success_message' => 'required',
        'recipients.*.email' => 'required',
    ];

    public $customMessages = [
        'recipients.*.email' => 'offline.forms::lang.recipients_email_validation',
    ];

    public $attributeNames = [
        'success_message' => 'offline.forms::lang.success_message',
    ];

    public $hasMany = ['submissions' => Submission::class];

    public $slugs = ['slug' => 'name'];

    public $jsonable = ['recipients', 'fields'];

    public $casts = [
        'spam_protection_enabled' => 'boolean',
        'spam_limit_ip_15min' => 'integer',
        'spam_limit_global_1h' => 'integer',
    ];

    public function beforeValidate()
    {
        if ($this->send_cc && !$this->getEmailField()) {
            throw new ValidationException([
                'send_cc' => trans('offline.forms::lang.validation_email_field_required'),
            ]);
        }
    }

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

                if (array_get($field, '_field_type') === 'fileupload') {
                    if ($maxFiles = array_get($field, 'max_files')) {
                        $rules[] = 'max:' . $maxFiles;
                    }
                }

                return count($rules) > 0
                    ? [$field['name'] => implode('|', $rules)]
                    : [];
            })
            ->filter()
            ->toArray();
    }

    /**
     * Returns the subject or the form name.
     */
    public function getMailSubjectAttribute(): string
    {
        return $this->subject ?: $this->name;
    }

    /**
     * Return field names for this form.
     */
    public function getFieldNames(): array
    {
        return collect($this->fields)
            ->mapWithKeys(fn(array $field) => [$field['name'] => $field['label'] ?? $field['name']])
            ->toArray();
    }

    /**
     * Returns all fields that may contain data (not sections)..
     */
    public function getRelevantFields(): array
    {
        return collect($this->fields)
            ->filter(fn(array $field) => $field['_field_type'] !== 'section')
            ->toArray();
    }

    /**
     * Returns the first e-mail field of the form.
     */
    public function getEmailField(): ?array
    {
        return collect($this->fields)
            ->first(fn(array $field) => ($field['type'] ?? '') === 'email');
    }
}
