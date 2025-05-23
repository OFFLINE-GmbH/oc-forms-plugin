<?php

namespace OFFLINE\Forms\Models;

use Closure;
use Model;
use October\Rain\Database\Relations\HasMany;
use October\Rain\Database\Scopes\MultisiteScope;
use ValidationException;

/**
 * @property string $name
 * @property string $success_message
 * @property array $fields
 * @property boolean $send_cc
 * @property boolean $is_enabled
 * @property boolean $is_archived
 * @property boolean $is_available
 * @property array{'email': string, 'name': string} $recipients
 * @property boolean $spam_protection_enabled
 * @property integer $spam_limit_ip_15min
 * @property integer $spam_limit_global_1h
 * @property bool $spam_use_captcha
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
    use \October\Rain\Database\Traits\Multisite;

    public $table = 'offline_forms_forms';

    public $propagatable = [
        'slug',
        'is_enabled',
        'is_archived',
        'recipients',
        'send_cc',
        'spam_protection_enabled',
        'spam_limit_ip_15min',
        'spam_limit_global_1h',
        'spam_use_captcha',
        'success_script',
    ];

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

    public $hasMany = [
        'submissions' => [
            Submission::class,
            'replicate' => false,
        ]
    ];

    public $slugs = ['slug' => 'name'];

    public $jsonable = ['recipients', 'fields'];

    public $casts = [
        'spam_protection_enabled' => 'boolean',
        'spam_limit_ip_15min' => 'integer',
        'spam_limit_global_1h' => 'integer',
    ];

    protected $propagatableSync = true;

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
        // Enabled forms can't be archived.
        if ($this->is_enabled) {
            $this->is_archived = false;
        }

        // Archived forms can't be enabled.
        if ($this->is_archived) {
            $this->is_enabled = false;
        }

        $this->setFieldNames();
    }

    public function scopeForAllSites($query)
    {
        $query->withoutGlobalScope(MultisiteScope::class);
    }

    public static function getFormOptions()
    {
        return self::orderBy('name')->pluck('name', 'id')->toArray();
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

                if (array_get($field, '_field_type') === 'checkboxlist') {
                    $rules[] = 'array|min:1';
                }

                if (array_get($field, 'type') === 'email') {
                    $rules[] = 'email';
                }

                if (array_get($field, '_field_type') === 'fileupload') {
                    if ($maxFiles = array_get($field, 'max_files')) {
                        $rules[] = 'max:' . $maxFiles;
                    }
                }

                if ($customRules = array_get($field, 'custom_validation_rules')) {
                    $rules = collect($rules)
                        ->merge(collect($customRules)->pluck('rule'))
                        ->unique()
                        ->toArray();
                }

                return count($rules) > 0
                    ? [$field['name'] => implode('|', $rules)]
                    : [];
            })
            ->filter()
            ->toArray();
    }

    /**
     * Return validation messages for this form.
     */
    public function getValidationMessages(): array
    {
        return collect($this->fields)
            ->mapWithKeys(function (array $field) {
                $messages = [];

                if ($customRules = array_get($field, 'custom_validation_rules')) {
                    collect($customRules)
                        ->each(function (array $rule) use (&$messages, $field) {
                            $key = sprintf('%s.%s', $field['name'], strtok($rule['rule'], ':'));
                            $messages[$key] = $rule['message'];
                        });
                }

                return $messages;
            })
            ->filter()
            ->toArray();
    }

    /**
     * True if the form is enabled and not archived.
     */
    public function getIsAvailableAttribute(): bool
    {
        return $this->is_enabled && !$this->is_archived;
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
            ->filter(fn(array $field) => array_get($field, '_field_type') !== 'section')
            ->toArray();
    }

    /**
     * Returns the first e-mail field of the form.
     */
    public function getEmailField(): ?array
    {
        return collect($this->fields)
            ->first(fn(array $field) => array_get($field, 'type') === 'email');
    }

    /**
     * Apply a callback to each field.
     */
    public function mapFields(Closure $callback): void
    {
        $fields = $this->fields;

        foreach ($fields as &$field) {
            $field = $callback($field);
        }

        unset($field);

        $this->fields = $fields;
    }

    /**
     * This helper method transfers all field names to the placeholder attribute
     * for fields that have no placeholder set.
     *
     * This is useful for "floating label" forms where a placeholder is required.
     *
     * You can provide an optional mutation function that will be called for each field.
     */
    public function applyPlaceholderToFields(?Closure $mutationFn = null): void
    {
        $this->mapFields(function (array $field) use ($mutationFn) {
            if (!array_get($field, 'placeholder')) {
                $field['placeholder'] = $field['label'] ?? '';

                if ($mutationFn) {
                    $field = $mutationFn($field);
                }
            }

            return $field;
        });
    }

    /**
     * Helper method to prepend a field to this form.
     */
    public function prependField(array $field): void
    {
        $fields = $this->fields;

        array_unshift($fields, $field);

        $this->fields = $fields;
    }

    /**
     * Helper method to append a field to this form.
     */
    public function appendField(array $field): void
    {
        $fields = $this->fields;

        $fields[] = $field;

        $this->fields = $fields;
    }

    /**
     * Safe data returns a safe-for-publication version of the form data.
     */
    public function getSafeData(): array
    {
        return [
            'slug' => $this->slug,
            'fields' => collect($this->fields)->keyBy('name')->toArray(),
        ];
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
}
