<?php

return [
    'plugin' => [
        'name' => 'Forms',
        'description' => 'Simple form builder plugin',
    ],
    'permissions' => [
        'is_admin' => 'Ist Formular-Admin',
        'is_admin_comment' => 'Kann die Formularkonfiguration anpassen',
        'can_see_forms' => 'Kann Formulare sehen',
        'can_see_forms_comment' => 'Sieht die verfügbaren Formulare und deren Einträge',
    ],
    'name' => 'Name',
    'forms' => 'Formulare',
    'recipients' => 'Empfänger',
    'add_recipient' => 'Empfänger hinzufügen',
    'email' => 'E-Mail',
    'send_cc' => 'Kopie an Absender senden',
    'send_cc_comment' => 'Sendet eine Kopie der E-Mail an den Absender des Formulars',
    'config' => 'Konfiguration',
    'is_enabled' => 'Ist aktiv',
    'is_enabled_comment' => 'Formular wird auf der Website angezeigt',
    'is_archived' => 'Ist archiviert',
    'is_archived_comment' => 'Archivierte Formulare werden im Backend nicht mehr angezeigt',
    'placeholder' => 'Platzhalter',
    'placeholder_comment' => 'Wird im leeren Feld angezeigt',
    'hide_archived' => 'Verstecke archivierte',
    'spam_protection' => 'Spamschutz',
    'spam_protection_enabled' => 'Spamschutz aktiv',
    'spam_protection_enabled_comment' => 'Verhindere Massenversand von E-Mails über dieses Formular',
    'spam_limit_ip_15min' => 'Max. Anzahl Nachrichten pro IP in 15 Minuten',
    'spam_limit_global_1h' => 'Max. Anzahl Nachrichten für dieses Formular in einer Stunde',
    'slug' => 'Slug',
    'recipients_email_validation' => 'Bitte geben Sie eine gültige Empfänger-E-Mail ein',
    'updated_at' => 'Aktualisiert',
    'created_at' => 'Erstellt',
    'deleted_at' => 'Gelöscht',
    'form_disabled' => 'Dieses Formular steht momentan nicht mehr zur Verfügung.',
    'export_submissions' => 'Einträge exportieren',
    'submissions' => 'Einträge',
    'fields' => 'Felder',
    'add_field' => 'Feld hinzufügen',
    'input' => 'Einzeiliges Textfeld',
    'textarea' => 'Mehrzeiliges Textfeld',
    'is_required' => 'Ist Pflichtfeld',
    'label' => 'Beschriftung',
    'label_comment' => 'Wird als Beschriftung im Formular verwendet',
    'name_comment' => 'Optional, wird automatisch generiert wenn leer',
    'fileupload' => 'Dateiupload',
    'title' => 'Titel',
    'subtitle' => 'Untertitel',
    'type' => 'Typ',
    'text' => 'Text',
    'text_input' => 'Einfacher Text',
    'subject' => 'Betreff',
    'subject_comment' => 'Dieser Text wird als E-Mail-Betreff verwendet. Wenn leer, wird der Formularname verwendet.',
    'ip_hash' => 'IP (gehashed)',
    'port' => 'Port',
    'boxes_section' => 'Formulare',
    'section' => 'Zwischentitel',
    'form' => 'Formular',
    'form_empty_option' => '-- Bitte wählen',
    'rows' => 'Anzahl Zeilen',
    'success_message' => 'Erfolgsmeldung',
    'success_message_comment' => 'Wird dem Absender nach dem Absenden des Formulars angezeigt',
    'validation_email_field_required' => 'Um eine Kopie an den Absender senden zu können, muss ein Feld mit vom Typ E-Mail im Formular vorhanden sein',
    'submit' => 'Absenden',
    'submit_button_label' => 'Text für Absende-Aktion',
    'submit_button_label_comment' => 'Wird auf dem Absende-Button angezeigt',
    'spam_protection_15_error' => 'Sie senden zu viele Nachrichten. Bitte versuchen Sie es in 15 Minuten erneut.',
    'spam_protection_honeypot' => 'Ihre Nachricht wurde als SPAM erkannt und gesperrt.',
    'spam_protection_global_error' => 'Das Formular ist vorübergehend deaktiviert. Bitte versuchen Sie es später erneut.',
    'submit_error' => 'Bitte korrigieren Sie Ihre Eingaben.',
    'fileupload_allowed_extensions' => 'Erlaubte Dateiendungen',
    'fileupload_allowed_extensions_comment' => 'z. B. jpg, jpeg, png, pdf, txt, leer lassen für keine Einschränkung',
    'fileupload_max_files' => 'Max. Anzahl Dateien',
    'fileupload_max_files_comment' => 'Es können maximal so viele Dateien hochgeladen werden, leer lassen für keine Einschränkung',
    'fileupload_max_size' => 'Max. Dateigröße (in MB)',
    'fileupload_max_size_comment' => 'Leer lassen für keine Einschränkung',
    'dropdown' => 'Auswahlliste',
    'options' => 'Optionen',
    'number' => 'Zahl',
    'date' => 'Datum',
    'time' => 'Uhrzeit',
    'components' => [
        'render_form' => [
            'name' => 'Formular',
            'description' => 'Zeigt ein Formular an',
            'id' => 'Zeige Formular',
            'form_classes' => 'CSS-Klassen für Formular',
            'css_prefix' => 'CSS-Klassen-Prefix',
            'use_honeypot' => 'Honeypot verwenden',
            'include_jquery' => 'jQuery automatisch einbinden',
            'include_framework' => 'AJAX Framework automatisch einbinden',
        ],
    ],
];
