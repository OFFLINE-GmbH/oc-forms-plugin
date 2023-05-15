<?php

namespace OFFLINE\Forms\Controllers;

use Backend;
use Backend\Classes\Controller;
use BackendMenu;
use OFFLINE\Forms\Models\Form;

class Submissions extends Controller
{
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class,
        \Backend\Behaviors\ImportExportController::class,
    ];

    public $formConfig = 'config_form.yaml';

    public $listConfig = 'config_list.yaml';

    public $importExportConfig = 'config_import_export.yaml';

    public $requiredPermissions = [
        'offline.forms::can_see_forms',
    ];

    protected Form $formModel;

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('OFFLINE.Forms', 'offline-forms-main-menu', 'side-menu-forms');

        $this->formModel = Form::query()
            ->where(function ($query) {
                $query
                    ->where('id', $this->params[0] ?? -1)
                    ->orWhere('site_root_id', $this->params[0] ?? -1);
            })
            ->firstOrFail();
    }

    public function index($formId = null)
    {
        BackendMenu::setContext('OFFLINE.Forms', 'offline-forms-main-menu', 'submissions-' . $formId);

        return $this->asExtension(Backend\Behaviors\ListController::class)->index($formId);
    }

    public function export($formId = null)
    {
        BackendMenu::setContext('OFFLINE.Forms', 'offline-forms-main-menu', 'submissions-' . $formId);

        return $this->asExtension(Backend\Behaviors\ImportExportController::class)->export();
    }

    public function update($formId = null, $submissionId = null)
    {
        BackendMenu::setContext('OFFLINE.Forms', 'offline-forms-main-menu', 'submissions-' . $formId);

        return $this->asExtension(Backend\Behaviors\FormController::class)->update($submissionId);
    }

    public function update_onSave($formId = null, $submissionId = null)
    {
        return $this->asExtension(Backend\Behaviors\FormController::class)->update_onSave($submissionId);
    }

    public function listExtendQuery($query)
    {
        $query->where(function ($query) {
            // Limit the query to submissions from the current form.
            $query->where('form_id', $this->formModel->id);

            // Include all submissions from the same form on other sites as well.
            $query->orWhereHas('form', function ($q) {
                $q->forAllSites()->where('site_root_id', $this->formModel->site_root_id);
            });
        });

        // Add the search query if a search term is set.
        $term = $this->getWidget('listToolbarSearch')?->getActiveTerm();

        if (!$term) {
            return;
        }

        $searchableColumns = collect($this->getWidget('list')?->getColumns() ?? [])
            ->filter(fn($column) => $column->_searchable)
            ->pluck('columnName');

        // Search in all searchable "data" values.
        $query->where(fn($q) => $searchableColumns->each(
            fn($column) => $q->orWhere("data->{$column}", 'like', "%${term}%")
        ));
    }

    public function listOverrideRecordUrl($record, $definition = null)
    {
        return "offline/forms/submissions/update/{$this->formModel->id}/{$record->id}";
    }

    public function formGetRedirectUrl($context = null, $model = null)
    {
        if (str_contains($context, 'close')) {
            return Backend::url('offline/forms/submissions/index/' . $this->formModel->id);
        }

        return Backend::url("offline/forms/submissions/update/{$this->formModel->id}/{$model->id}");
    }

    public function formCreateModelObject()
    {
        $model = $this->asExtension(Backend\Behaviors\FormController::class)->formCreateModelObject();

        $model->form_id = $this->formModel->id;

        return $model;
    }

    public function getFormModel()
    {
        return $this->formModel;
    }
}
