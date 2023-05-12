<?php namespace OFFLINE\Forms\Models;

use Illuminate\Mail\Message;
use October\Rain\Argon\Argon;
use October\Rain\Database\ExpandoModel;
use October\Rain\Database\Relations\BelongsTo;
use October\Rain\Support\Facades\Mail;

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

    public function afterCreate()
    {
        if (count($this->form->recipients ?? []) > 0) {
            $this->sendMailToRecipients($this->form->name, $this->form->recipients);
        }

        if ($this->form->send_cc && $mailField = $this->form->getEmailField()) {
            // Get the recipient's email from the form data.
            $email = $this->data[$mailField['name']] ?? null;
            if ($email) {
                $this->sendMailToRecipients($this->form->name, [['email' => $email]]);
            }
        }
    }

    public function beforeValidate()
    {
        $this->rules = array_merge($this->rules, $this->form->getValidationRules());
        $this->attributeNames = array_merge($this->attributeNames, $this->form->getFieldNames());
    }

    /**
     * Send the submission email to all provided recipients.
     */
    public function sendMailToRecipients(string $subject, array $recipients)
    {
        Mail::send(
            'offline.forms::mail.submission',
            ['submission' => $this],
            function (Message $message) use ($subject, $recipients) {
                $message->subject($subject);
                foreach ($recipients as $recipient) {
                    $message->to(array_get($recipient, 'email'), array_get($recipient, 'name'));
                }
            });
    }
}
