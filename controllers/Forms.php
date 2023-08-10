<?php

namespace OFFLINE\Forms\Controllers;

use Backend\Classes\Controller;
use Backend\Widgets\Form;
use BackendMenu;

class Forms extends Controller
{
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class,
    ];

    public $turboVisitControl = 'reload';

    public $formConfig = 'config_form.yaml';

    public $listConfig =  'config_list.yaml';

    public $requiredPermissions = [
        'offline.forms::is_admin',
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('OFFLINE.Forms', 'offline-forms-main-menu', 'side-menu-forms');
    }

    public function onDuplicate()
    {
        $forms = \OFFLINE\Forms\Models\Form::whereIn('id', post('checked'))->get();
        foreach ($forms as $form) {
            $newForm = $form->replicate();
            $newForm->name .= ' - Copy';
            $newForm->save();
        }

        \Flash::success(\Lang::get('offline.forms::lang.duplicate_success'));
        return $this->listRefresh();
    }

    public function formExtendFieldsBefore(Form $form)
    {
        // Remove the file upload fields if Responsiv.Uploader is not installed.
        if (class_exists(\Responsiv\Uploader\Plugin::class)) {
            return;
        }

        unset($form->tabs['fields']['fields']['groups']['fileupload']);
    }

    public function listInjectRowClass($record)
    {
        if ($record->is_archived) {
            return 'safe strike';
        }

        if (!$record->is_enabled) {
            return 'disabled';
        }
    }
}
