<?php

return [
    'plugin' => [
        'name' => 'Forms',
        'description' => 'Simple form builder plugin',
    ],
    'permissions' => [
        'is_admin' => 'Is form admin',
        'is_admin_comment' => 'Can change the form configuration',
        'can_see_forms' => 'Can see form submissions',
        'can_see_forms_comment' => 'Can see form submissions in the backend',
    ],
    'name' => 'Name',
    'forms' => 'Forms',
    'recipients' => 'Recipients',
    'add_recipient' => 'Add Recipient',
    'email' => 'E-Mail',
    'send_cc' => 'Send a copy to the sender',
    'send_cc_comment' => 'Send a copy of the email to the sender of the form',
    'config' => 'Configuration',
    'is_enabled' => 'Is enabled',
    'is_enabled_comment' => 'Form will be displayed on the website',
    'is_archived' => 'Is archived',
    'is_archived_comment' => 'Archived forms are hidden in the backend',
    'placeholder' => 'Placeholder',
    'placeholder_comment' => 'Is displayed in the empty field',
    'hide_archived' => 'Hide archived',
    'spam_protection' => 'SPAM Protection',
    'spam_protection_enabled' => 'Enable SPAM Protection',
    'spam_protection_enabled_comment' => 'Prevent mass email sending through this form',
    'spam_limit_ip_15min' => 'Max. number of messages per IP in 15 minutes',
    'spam_limit_global_1h' => 'Max. number of messages per hour for this form',
    'spam_use_captcha' => 'Use Captcha',
    'spam_use_captcha_comment' => 'Users have to solve a captcha to submit the form',
    'slug' => 'Slug',
    'recipients_email_validation' => 'Please enter a valid recipient email address',
    'updated_at' => 'Updated',
    'created_at' => 'Created',
    'deleted_at' => 'Deleted',
    'form_disabled' => 'This form is currently unavailable.',
    'export_submissions' => 'Export submissions',
    'submissions' => 'Submissions',
    'fields' => 'Fields',
    'add_field' => 'Add field',
    'input' => 'Text field',
    'textarea' => 'Textarea',
    'checkboxlist' => 'Checkbox list',
    'checkboxlist_comment' => 'Select multiple options',
    'is_required' => 'Is required',
    'label' => 'Label',
    'label_comment' => 'Is displayed next to the field on the website',
    'name_comment' => 'Optional, will be generated from the label if left empty',
    'fileupload' => 'File upload',
    'title' => 'Title',
    'subtitle' => 'Subtitle',
    'type' => 'Type',
    'text' => 'Text',
    'text_input' => 'Simple text input',
    'subject' => 'Subject',
    'subject_comment' => 'This text will be used as e-mail subject. If left empty, the form name will be used.',
    'ip_hash' => 'IP (hashed)',
    'port' => 'Port',
    'boxes_section' => 'Forms',
    'section' => 'Section',
    'form' => 'Form',
    'form_empty_option' => '-- Select',
    'rows' => 'Rows',
    'success_message' => 'Success message',
    'success_message_comment' => 'Displayed after the form has been sent successfully',
    'success_script' => 'Success script',
    'success_script_comment' => 'Will be executed after the form has been sent successfully. Use the variables `window.ocForms.data` and `window.ocForms.submission` to access the form data.',
    'validation_email_field_required' => 'To send a copy to the sender, the form must have at least one e-mail fieldTo send a copy to the sender, the form must have at least one e-mail field.',
    'submit' => 'Submit',
    'submit_button_label' => 'Text for submit button',
    'submit_button_label_comment' => 'Will be displayed on the submit button.',
    'spam_protection_15_error' => 'You are sending too many messages. Please try again in 15 minutes.',
    'spam_protection_honeypot' => 'Your message has been rejected as SPAM.',
    'spam_protection_global_error' => 'This form is currently unavailable. Please try again later.',
    'submit_error' => 'Please check the form for errors.',
    'fileupload_allowed_extensions' => 'Allowed extensions',
    'fileupload_allowed_extensions_comment' => 'e.g. jpg, jpeg, png, pdf, txt, leave blank for no limitations',
    'fileupload_max_files' => 'Max. number of files',
    'fileupload_max_files_comment' => 'Leave blank for no limitations',
    'fileupload_max_size' => 'Max. file size (in MB)',
    'fileupload_max_size_comment' => 'Leave blank for no limitations',
    'dropdown' => 'Dropdown',
    'dropdown_comment' => 'Select one option',
    'options' => 'Options',
    'number' => 'Number',
    'date' => 'Date',
    'time' => 'Time',
    'components' => [
        'render_form' => [
            'name' => 'Form',
            'description' => 'Displays a form',
            'id' => 'Form ID',
            'form_classes' => 'CSS classes for this form',
            'css_prefix' => 'CSS class prefix',
            'use_honeypot' => 'Use Honeypot',
            'include_jquery' => 'Include jQuery',
            'include_framework' => 'Include AJAX Framework',
        ],
    ],
    'duplicate_success' => 'Form has been duplicated',
    'validation_section' => 'Validation',
    'custom_validation_rules' => 'Custom validation rules',
    'custom_validation_rules_comment' => 'Here you can define custom validation rules for this field. Format: https://docs.octobercms.com/3.x/extend/services/validation',
    'add_custom_validation_rule' => 'Add rule',
    'custom_validation_rule' => 'Rule',
    'custom_validation_message' => 'Error message',
    'captcha_validation' => 'The captcha is not correct. Please try again.',
    'captcha_regenerate' => 'Regenerate captcha',
    'radio' => 'Radio buttons',
    'radio_comment' => 'Select one option',
];
