<?php

namespace OFFLINE\Forms\Models;

use Illuminate\Mail\Message;
use October\Rain\Argon\Argon;
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

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

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
            $this->created_at = Argon::now();
        }

        if (!$this->updated_at) {
            $this->updated_at = Argon::now();
        }
    }

    public function afterCreate()
    {
        // commit the deferred bindings here so that the attachment is available when sending mails.
        $this->commitDeferred($this->sessionKey);

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
    }

    public function beforeValidate()
    {
        $this->rules = array_merge($this->rules, $this->form->getValidationRules());
        $this->customMessages = $this->form->getValidationMessages();
        $this->attributeNames = array_merge($this->attributeNames, $this->form->getFieldNames());
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

        Mail::send(
            'offline.forms::mail.submission',
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
