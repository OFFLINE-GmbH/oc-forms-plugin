<?php namespace OFFLINE\Forms\Controllers;

use Backend;
use BackendMenu;
use Backend\Classes\Controller;
use OFFLINE\Forms\Models\Form;

class Submissions extends Controller
{
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public $requiredPermissions = [
        'offline.forms::can_see_forms'
    ];

    protected Form $formModel;

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('OFFLINE.Forms', 'main-menu-item', 'side-menu-forms');

        $this->formModel = Form::findOrFail($this->params[0] ?? -1);
    }

    public function index($recordId = null)
    {
        BackendMenu::setContext('OFFLINE.Forms', 'main-menu-item', 'submissions-' . $recordId);

        return $this->asExtension(Backend\Behaviors\ListController::class)->index($recordId);
    }

    public function listExtendColumns(Backend\Widgets\Lists $widget)
    {
        $widget->addColumns([
            'x' => [
                'label' => 'x'
            ]
        ]);
    }

    public function listExtendQuery($query)
    {
        $query->where('form_id', $this->formModel->id);
    }

}
