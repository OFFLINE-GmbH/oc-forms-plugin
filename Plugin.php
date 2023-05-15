<?php

namespace OFFLINE\Forms;

use Backend\Classes\NavigationManager;
use Backend\Facades\Backend;
use Illuminate\Support\Facades\Event;
use OFFLINE\Forms\Classes\Contexts;
use OFFLINE\Forms\Classes\Events;
use OFFLINE\Forms\Models\Form;
use OFFLINE\Forms\Models\Submission;
use System\Classes\PluginBase;
use System\Models\File;

class Plugin extends PluginBase
{
    public function boot()
    {
        // Add a submenu item for each Form in the backend menu.
        Event::listen('backend.menu.extendItems', function (NavigationManager $navigationManager) {
            $forms = Form::where('is_archived', false)->get();

            // Change the URL of the main menu item to the first form.
            $item = $navigationManager->getMainMenuItem('OFFLINE.Forms', 'offline-forms-main-menu');

            if ($item && $firstForm = $forms->first()) {
                $item->url = Backend::url('offline/forms/forms/submissions/' . $firstForm->id);
            }

            $forms->each(function (Form $form) use ($navigationManager) {
                $navigationManager->addSideMenuItems('OFFLINE.Forms', 'offline-forms-main-menu', [
                    'submissions-' . $form->id => [
                        'label' => $form->name,
                        'icon' => 'icon-list',
                        'permissions' => ['offline.forms::can_see_forms'],
                        'url' => Backend::url('offline/forms/submissions/index/' . $form->id),
                    ],
                ]);
            });
        });

        // Add fields to the submission form.
        Event::listen('backend.form.extendFields', function (\Backend\Widgets\Form $widget) {
            if (!$widget->model instanceof Submission) {
                return;
            }

            // Allow other plugins to modify the fields.
            Event::fire(Events::FORM_EXTEND, [$widget->model->form, Contexts::FIELDS, $widget]);

            collect($widget->model->form->fields)->each(function (array $field) use ($widget) {
                $args = [];

                switch ($field['_field_type'] ?? '') {
                    case 'section':
                        $args['type'] = 'section';
                        $args['span'] = 'full';
                        $args['comment'] = $field['text'];
                        break;
                    case 'fileupload':
                        $allowedExtensions = array_get($field, 'allowed_extensions');
                        $args['type'] = 'fileupload';
                        $args['mode'] = array_intersect(explode(',', $allowedExtensions), ['jpg', 'jpeg', 'png', 'gif']) ? 'image' : 'file';
                        $args['fileTypes'] = $allowedExtensions;
                        break;
                    default:
                        $args['type'] = 'text';
                        break;
                }
                $widget->addTabFields([
                    $field['name'] => [
                        'label' => $field['label'] ?? $field['name'],
                        'span' => 'auto',
                        'tab' => 'offline.forms::lang.fields',
                        ...$args,
                    ],
                ]);
            });
        });

        // Add columns to the submission list.
        Event::listen('backend.list.extendColumns', function (\Backend\Widgets\Lists $widget) {
            if (!$widget->model instanceof Submission) {
                return;
            }

            $form = $widget->getController()->getFormModel();

            if (!$form) {
                return;
            }

            // Allow other plugins to modify the fields.
            Event::fire(Events::FORM_EXTEND, [$form, Contexts::COLUMNS, $widget]);

            collect($form->fields)->each(function (array $field) use ($widget) {
                $args = [];

                switch ($field['_field_type'] ?? '') {
                    case 'section':
                        return;
                    case 'fileupload':
                        $args['type'] = 'partial';
                        $args['path'] = '$/offline/forms/controllers/submissions/_fileupload_column.php';
                        $args['clickable'] = false;
                        break;
                    default:
                        $args['type'] = 'text';
                        break;
                }

                $widget->addColumns([
                    $field['name'] => [
                        'label' => $field['label'] ?? $field['name'],
                        'sortable' => false,
                        '_searchable' => true,
                        ...$args,
                    ],
                ]);

                // Add the default columns to the end.
                $widget->removeColumn('created_at');

                $widget->addColumns([
                    'ip_hash' => [
                        'label' => 'offline.forms::lang.ip_hash',
                        'type' => 'string',
                        'invisible' => true,
                    ],
                    'port' => [
                        'label' => 'offline.forms::lang.port',
                        'type' => 'string',
                        'invisible' => true,
                    ],
                    'created_at' => [
                        'label' => 'offline.forms::lang.created_at',
                        'type' => 'date',
                    ],
                    'updated_at' => [
                        'label' => 'offline.forms::lang.updated_at',
                        'type' => 'date',
                        'invisible' => 'true',
                    ],
                    'deleted_at' => [
                        'label' => 'offline.forms::lang.deleted_at',
                        'type' => 'date',
                        'invisible' => 'true',
                    ],
                ]);
            });
        });

        // Override the export column value for file uploads.
        Event::listen('backend.list.overrideColumnValueRaw', function ($listWidget, $record, $column, &$value) {
            if (!$listWidget->model instanceof Submission) {
                return;
            }

            if ($listWidget->getController()->getAction() !== 'export') {
                return;
            }

            if (array_get($column->config, 'path') !== '$/offline/forms/controllers/submissions/_fileupload_column.php') {
                return;
            }

            $value = $value->implode(fn (File $file) => "{$file->getPath()} ({$file->getFilename()})", ',');
        });
    }

    /**
     * registerComponents used by the frontend.
     */
    public function registerComponents()
    {
        return [
            \OFFLINE\Forms\Components\RenderForm::class => 'renderForm',
        ];
    }

    /**
     * registerSettings used by the backend.
     */
    public function registerSettings()
    {
    }
}
