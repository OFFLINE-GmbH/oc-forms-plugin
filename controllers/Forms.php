<?php namespace OFFLINE\Forms\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Forms extends Controller
{
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class
    ];

    public $turboVisitControl = 'reload';

    public $formConfig = 'config_form.yaml';
    public $listConfig =  'config_list.yaml';

    public $requiredPermissions = [
        'offline.forms::is_admin'
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('OFFLINE.Forms', 'main-menu-item', 'side-menu-forms');
    }
}
