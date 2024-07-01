<?php

namespace OFFLINE\Forms\Components;

use Cms\Classes\ComponentBase;
use October\Rain\Argon\Argon;
use October\Rain\Database\Scopes\MultisiteScope;
use October\Rain\Exception\ValidationException;
use October\Rain\Support\Facades\Event;
use October\Rain\Support\Facades\Str;
use OFFLINE\Forms\Classes\Contexts;
use OFFLINE\Forms\Classes\Events;
use OFFLINE\Forms\Models\Form;
use OFFLINE\Forms\Models\Submission;
use Responsiv\Uploader\Components\FileUploader;

/**
 * Renders a form.
 */
class RenderForm extends ComponentBase
{
    /**
     * The form that is rendered.
     */
    public ?Form $form;

    /**
     * Add a honeypot field to the form.
     *
     * The field has to be made invisible via CSS (.visually-hidden).
     * If it is submitted with content, the form is rejected as spam.
     */
    public bool $useHoneypot = true;

    /**
     * Prefix all CSS classes with this string.
     */
    public string $cssClassPrefix = '';

    /**
     * Label for the Responsiv.Uploader upload button.
     */
    public string $fileuploadPlaceholderText = '';

    /**
     * The Captcha instance.
     */
    public array $captcha = [];

    /**
     * Details of this component.
     */
    public function componentDetails()
    {
        return [
            'name' => 'offline.forms::lang.components.render_form.name',
            'description' => 'offline.forms::lang.components.render_form.description',
        ];
    }

    /**
     * Available properties.
     */
    public function defineProperties(): array
    {
        return [
            'id' => [
                'title' => 'offline.forms::lang.components.render_form.id',
                'type' => 'dropdown',
            ],
            'formClasses' => [
                'title' => 'offline.forms::lang.components.render_form.form_classes',
                'type' => 'string',
                'default' => '',
            ],
            'cssPrefix' => [
                'title' => 'offline.forms::lang.components.render_form.css_prefix',
                'type' => 'string',
                'default' => '',
            ],
            'useHoneypot' => [
                'title' => 'offline.forms::lang.components.render_form.use_honeypot',
                'type' => 'checkbox',
                'default' => true,
            ],
            'includeJQuery' => [
                'title' => 'offline.forms::lang.components.render_form.include_jquery',
                'type' => 'checkbox',
                'default' => false,
            ],
            'includeFramework' => [
                'title' => 'offline.forms::lang.components.render_form.include_framework',
                'type' => 'checkbox',
                'default' => false,
            ],
            'fileuploadPlaceholderText' => [
                'title' => 'offline.forms::lang.components.render_form.fileupload_placeholder_text',
                'type' => 'string',
                'default' => '',
            ],
        ];
    }

    /**
     * The component is initialized.
     */
    public function init()
    {
        $this->useHoneypot = $this->property('useHoneypot', true);
        $this->cssClassPrefix = $this->property('cssPrefix', '');
        $this->fileuploadPlaceholderText = $this->property('fileuploadPlaceholderText');

        if ($this->property('includeJQuery', false)) {
            $this->addJs('/modules/system/assets/js/vendor/jquery.min.js');
        }

        if ($this->property('includeFramework', false)) {
            $this->addJs('/modules/system/assets/js/framework-extras.js');
        }

        $this->setupForm();
    }

    /**
     * OFFLINE.Boxes compatibility.
     */
    public function boxesInit()
    {
        $this->setupForm();
    }

    /**
     * Handle form submission.
     *
     * @throws ValidationException
     */
    public function onSubmit()
    {
        if (!$this->form) {
            return;
        }

        if (!$this->form->is_available) {
            throw new ValidationException(['_global' => 'This form is currently unavailable.']);
        }

        $this->guardSpamSubmissions();

        $submission = $this->getSubmissionModel();

        $data = array_except(request($this->alias), $submission->getGuarded());

        // Cleanup Array values.
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = array_values($value);
            }
        }

        $submission->forceFill($data);

        $submission->save(null, post('_session_key'));

        // Make the submission available in the success partial.
        $this->page['submission'] = $submission;
    }

    /**
     * Return all available forms as dropdown options.
     */
    public function getIdOptions()
    {
        return Form::getFormOptions();
    }

    /**
     * Prefix CSS classes.
     */
    public function classNames(string ...$classNames): string
    {
        return collect($classNames)
            ->map(function (string $classNames) {
                $classes = explode(' ', $classNames);

                return collect($classes)
                    ->map(fn (string $class) => "{$this->cssClassPrefix}{$class}")
                    ->implode(' ');
            })
            ->implode(' ');
    }

    /**
     * Helper method to convert strings to pascal case.
     */
    public function pascalCase(string $input): string
    {
        return ucfirst(Str::camel($input));
    }

    /**
     * Initialize the Captcha.
     */
    public function initializeCaptcha()
    {
        if (!$this->form->spam_use_captcha || !class_exists(\Mews\Captcha\Facades\Captcha::class)) {
            return;
        }

        $this->captcha = \Mews\Captcha\Facades\Captcha::create('default', true);
    }

    /**
     * Generate a new Captcha.
     */
    public function onRegenerateCaptcha()
    {
        $this->initializeCaptcha();

        return [
            '.' . $this->alias . '-captcha-container' => $this->renderPartial($this->alias . '::captcha_image'),
        ];
    }

    /**
     * Load and initialize the form.
     */
    protected function setupForm()
    {
        $this->form = $this->getForm();

        $this->initializeFileUploads();
        $this->initializeCaptcha();
    }

    /**
     * Initialize Responsiv.Uploader if available.
     */
    protected function initializeFileUploads()
    {
        if (!class_exists(\Responsiv\Uploader\Plugin::class) || !$this->form) {
            return;
        }

        $submission = $this->getSubmissionModel();

        foreach ($this->form->fields as $field) {
            if (array_get($field, '_field_type') !== 'fileupload') {
                continue;
            }

            $component = $this->controller->addComponent(
                FileUploader::class,
                'fileUploader' . $this->pascalCase($field['name']),
                [
                    'deferredBinding' => true,
                    'fileTypes' => array_get($field, 'allowed_extensions'),
                    'maxSize' => array_get($field, 'max_size'),
                    'form' => $this->form,
                    'field' => $field['name'],
                    'placeholderText' => $this->fileuploadPlaceholderText,
                ],
            );

            if (!$component) {
                continue;
            }

            $component->bindModel($field['name'], $submission);
            $component->onRun();
        }
    }

    /**
     * Enforce SPAM limits.
     */
    protected function guardSpamSubmissions()
    {
        if ($this->useHoneypot && post('_hp')) {
            throw new ValidationException([
                '_global' => trans('offline.forms::lang.spam_protection_honeypot'),
            ]);
        }

        if (!$this->form->spam_protection_enabled) {
            return;
        }

        $messageCountIp = Submission::query()
            ->where('ip_hash', hash('sha256', request()?->ip()))
            ->where('form_id', $this->form->id)
            ->where('created_at', '>=', Argon::now()->subMinutes(15))
            ->count();

        if ($messageCountIp > $this->form->spam_limit_ip_15min) {
            throw new ValidationException([
                '_global' => trans('offline.forms::lang.spam_protection_15_error'),
            ]);
        }

        $messageCountGlobal = Submission::query()
            ->where('form_id', $this->form->id)
            ->where('created_at', '>=', Argon::now()->subHour())
            ->count();

        if ($messageCountGlobal > $this->form->spam_limit_global_1h) {
            throw new ValidationException([
                '_global' => trans('offline.forms::lang.spam_protection_global_error'),
            ]);
        }
    }

    /**
     * Resolve the form based on the configured id.
     */
    protected function getForm(): ?Form
    {
        // Check if this component is rendered inside a OFFLINE.Boxes Box.
        if ($this->methodExists('getBoxesBox')) {
            $boxesBox = $this->getBoxesBox();

            if ($boxesBox->form) {
                $this->setProperty('id', $boxesBox->form);
            }
        }

        $form = Form::query()
            ->withoutGlobalScope(MultisiteScope::class)
            ->where(
                fn ($q) => $q
                    ->where('id', $this->property('id'))
                    ->orWhere('slug', $this->property('id'))
            )
            ->first();

        if (!$form) {
            return null;
        }

        Event::fire(Events::FORM_EXTEND, [&$form, Contexts::COMPONENT, $this]);

        Event::fire(Events::FORM_BEFORE_RENDER, [&$form, $this]);

        $form->fields = $this->processFormFields($form->fields);

        return $form;
    }

    /**
     * Add special attributes like a unique ID to the form fields.
     */
    protected function processFormFields(array $fields): array
    {
        return collect($fields)
            ->map(function (array $field) {
                $field['id'] = "{$this->alias}[{$field['name']}]";

                return $field;
            })
            ->toArray();
    }

    /**
     * Returns an initialized Submission model.
     */
    protected function getSubmissionModel()
    {
        $model = new Submission();
        $model->form_id = $this->form->id;
        $model->setRelationsForForm($this->form);

        return $model;
    }
}
