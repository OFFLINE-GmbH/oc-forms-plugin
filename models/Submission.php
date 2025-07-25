<?php

namespace OFFLINE\Forms\Models;

use Carbon\Carbon;
use Illuminate\Mail\Message;
use October\Rain\Database\ExpandoModel;
use October\Rain\Database\Relations\BelongsTo;
use October\Rain\Support\Facades\Event;
use October\Rain\Support\Facades\Mail;
use OFFLINE\Forms\Classes\Contexts;
use OFFLINE\Forms\Classes\Events;
use System\Models\File;

/**
 * @property string $ip_hash
 * @property string $port
 * @property int $form_id
 * @property Carbon $mail_sent_at
 *
 * @property Form $form
 * @method BelongsTo form()
 */
class Submission extends ExpandoModel
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\SoftDelete;

    public $table = 'offline_forms_submissions';

    public $rules = [
        'form_id' => 'required|exists:offline_forms_forms,id',
    ];

    public array $customMessages = [];

    public array $attributeNames = [];

    public $belongsTo = [
        'form' => [Form::class, 'scope' => 'forAllSites'],
    ];

    protected $expandoColumn = 'data';

    protected $expandoPassthru = [
        'form_id',
        'ip_hash',
        'port',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $dates = ['created_at', 'updated_at', 'deleted_at', 'mail_sent_at'];

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

    public function afterFetch()
    {
        if ($this->form) {
            $this->setRelationsForForm($this->form);

            $this->addDates();
        }
    }

    public function beforeCreate()
    {
        $this->ip_hash = hash('sha256', request()?->ip());
        $this->port = request()?->getPort();

        if (!$this->created_at) {
            $this->created_at = Carbon::now();
        }

        if (!$this->updated_at) {
            $this->updated_at = Carbon::now();
        }
    }

    public function afterCreate()
    {
        // commit the deferred bindings here so that the attachment is available when sending mails.
        $this->commitDeferred($this->sessionKey);

        // Prevent sending the mail if it was sent before.
        if ($this->mail_sent_at) {
            return;
        }

        if (count($this->form->recipients ?? []) > 0) {
            $this->sendMailToRecipients($this->form->name, $this->form->recipients);
        }

        if ($this->form->send_cc && $mailField = $this->form->getEmailField()) {
            // Get the recipient's email from the form data.
            $email = $this->data[$mailField['name']] ?? null;

            if ($email) {
                $this->sendMailToRecipients($this->form->mail_subject, recipients: [['email' => $email]], isCC: true);
            }
        }

        $this->mail_sent_at = Carbon::now();
        $this->saveQuietly(['force' => true]);
    }

    public function beforeValidate()
    {
        $this->rules = array_merge($this->rules, $this->form->getValidationRules());
        $this->customMessages = $this->form->getValidationMessages();
        $this->attributeNames = array_merge($this->attributeNames, $this->form->getFieldNames());

        if ($this->form->spam_use_captcha) {
            $this->rules['captcha'] = 'required|captcha_api:' . $this->captcha_key;
            $this->attributeNames['captcha'] = 'Captcha';
            $this->customMessages['captcha_api'] = trans('offline.forms::lang.captcha_validation');
        }
    }

    public function afterValidate()
    {
        // Add a generic error to display beside the submit button.
        if ($this->validationErrors->any() && !$this->validationErrors->has('submit')) {
            $this->validationErrors->add('_global', trans('offline.forms::lang.submit_error'));
        }
    }

    /**
     * Send the submission email to all provided recipients.
     */
    public function sendMailToRecipients(string $subject, array $recipients, bool $isCC = false)
    {
        Event::fire(Events::FORM_EXTEND, [&$this->form, Contexts::MAIL, null]);

        // Transfer the data attributes.
        $this->expandoAfterFetch();

        // Allow to override the mail view.
        $viewOverride = Event::fire(Events::SUBMISSION_OVERRIDE_MAIL_VIEW, [$this]);

        $view = 'offline.forms::mail.submission';
        if (is_array($viewOverride)) {
            $viewOverride = array_filter($viewOverride);
            if (count($viewOverride) > 0)  {
                $view = $viewOverride[0];
            }
        }

        Mail::send(
            $view,
            [
                'submission' => $this,
                'subject' => $subject,
                'isCC' => $isCC,
            ],
            function (Message $message) use ($subject, $recipients) {
                $message->subject($subject);

                foreach ($recipients as $recipient) {
                    $message->to(array_get($recipient, 'email'), array_get($recipient, 'name'));
                }
            }
        );
    }

    /**
     * Initialize dynamic relations.
     */
    public function setRelationsForForm(Form $form)
    {
        collect($form->fields)
            ->filter(fn ($field) => array_get($field, '_field_type') === 'fileupload')
            ->each(function ($field) {
                $this->attachMany[$field['name']] = [
                    File::class,
                    'public' => false,
                ];
            });
    }

    /**
     * Add all date fields to the $dates array.
     */
    private function addDates()
    {
        collect($this->form->fields)
            ->filter(fn ($field) => array_get($field, 'type') === 'date' && array_get($field, 'name'))
            ->each(fn ($field) => $this->dates[] = $field['name']);
    }
}
