<?php namespace OFFLINE\Forms;

use Backend\Classes\NavigationManager;
use Backend\Facades\Backend;
use Illuminate\Support\Facades\Event;
use OFFLINE\Forms\Models\Form;
use OFFLINE\Forms\Models\Submission;
use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public function register()
    {
        // Register OFFLINE.Boxes partials.
        \October\Rain\Support\Facades\Event::listen(
            \OFFLINE\Boxes\Classes\Events::REGISTER_PARTIAL_PATH,
            fn() => ['$/plugins/offline/forms/partials']
        );
    }

    public function boot()
    {
        // Add a submenu item for each Form in the backend menu.
        Event::listen('backend.menu.extendItems', function (NavigationManager $navigationManager) {
            $forms = Form::get();

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

            collect($widget->model->form->fields)->each(function (array $field) use ($widget) {
                $widget->addTabFields([
                    $field['name'] => [
                        'label' => $field['label'] ?? $field['name'],
                        'type' => $field['type'] ?? 'text',
                        'span' => 'auto',
                        'tab' => 'offline.forms::lang.fields',
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

            collect($form->fields)->each(function (array $field) use ($widget) {
                switch ($field['type']) {
                    default:
                        $type = 'text';
                        break;
                }

                $widget->addColumns([
                    $field['name'] => [
                        'label' => $field['label'] ?? $field['name'],
                        'type' => $type,
                        'sortable' => false,
                        '_searchable' => true,
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
