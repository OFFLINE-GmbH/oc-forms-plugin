<?php namespace OFFLINE\Forms\Components;

use Cms\Classes\ComponentBase;
use October\Rain\Argon\Argon;
use October\Rain\Exception\ValidationException;
use October\Rain\Support\Facades\Str;
use OFFLINE\Forms\Models\Form;
use OFFLINE\Forms\Models\Submission;
use Responsiv\Uploader\Components\FileUploader;
use System\Models\File;

/**
 * Renders a form.
 */
class RenderForm extends ComponentBase
{
    /**
     * The form that is rendered.
     */
    public Form $form;

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
        ];
    }

    /**
     * The component is initialized.
     */
    public function init()
    {
        $this->useHoneypot = $this->property('useHoneypot', true);
        $this->cssClassPrefix = $this->property('cssPrefix', '');

        $this->form = $this->getForm();

        $this->initializeFileUploads();
    }

    /**
     * Initialize Responsiv.Uploader if available.
     */
    protected function initializeFileUploads()
    {
        if (!class_exists(\Responsiv\Uploader\Plugin::class) || !$this->form) {
            return;
        }

        foreach ($this->form->fields as $field) {
            if ($field['_field_type'] !== 'fileupload') {
                continue;
            }

            $component = $this->controller->addComponent(
                FileUploader::class,
                'fileUploader' . $this->pascalCase($field['name']),
                [
                    'deferredBinding' => true,
                    'fileTypes' => array_get($field, 'types'),
                    'maxSize' => array_get($field, 'maxSize'),
                    'form' => $this->form,
                    'field' => $field['name'],
                ],
            );
            if (!$component) {
                continue;
            }

            $model = new Submission();
            $model->attachMany[$field['name']] = \System\Models\File::class;

            $component->bindModel($field['name'], $model);
        }
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

        $this->guardSpamSubmissions();

        $submission = new Submission();
        $submission->attachMany['bild'] = File::class;
        $submission->setRelation('bild', $submission->bild()->withDeferred(post('_session_key'))->get());
        $submission->form_id = $this->form->id;
        $submission->forceFill(array_except(request($this->alias), $submission->getGuarded()));
        $submission->save(null, post('_session_key'));
    }

    /**
     * Enforce SPAM limits.
     */
    protected function guardSpamSubmissions()
    {
        if ($this->useHoneypot && post('_hp')) {
            throw new ValidationException([
                'submit' => trans('offline.forms::lang.spam_protection_honeypot'),
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
                'submit' => trans('offline.forms::lang.spam_protection_15_error'),
            ]);
        }

        $messageCountGlobal = Submission::query()
            ->where('form_id', $this->form->id)
            ->where('created_at', '>=', Argon::now()->subHour())
            ->count();

        if ($messageCountGlobal > $this->form->spam_limit_global_1h) {
            throw new ValidationException([
                'submit' => trans('offline.forms::lang.spam_protection_global_error'),
            ]);
        }
    }

    /**
     * Return all available forms as dropdown options.
     */
    public function getIdOptions()
    {
        return Form::getFormOptions();
    }

    /**
     * Resolve the form based on the configured id.
     */
    protected function getForm(): ?Form
    {
        // Check if this component is rendered inside a OFFLINE.Boxes Box.
        if ($this->methodExists('getBoxesBox')) {
            $boxesBox = $this->getBoxesBox();

            $this->setProperty('id', $boxesBox->form);
        }

        $form = Form::find($this->property('id'));
        if (!$form) {
            return null;
        }

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
     * Prefix CSS classes.
     */
    public function classNames(string ...$classNames): string
    {
        return collect($classNames)
            ->map(function (string $classNames) {
                $classes = explode(' ', $classNames);

                return collect($classes)
                    ->map(function (string $class) {
                        return "$this->cssClassPrefix{$class}";
                    })
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
}
