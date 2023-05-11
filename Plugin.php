<?php namespace OFFLINE\Forms;

use Backend\Classes\NavigationManager;
use Backend\Facades\Backend;
use Illuminate\Support\Facades\Event;
use OFFLINE\FormBuilder\Models\Form;
use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public function boot()
    {
        $forms = Form::get();

        // Add a submenu item for each Form in the backend menu.
        Event::listen('backend.menu.extendItems', function (NavigationManager $navigationManager) use ($forms) {
            // Change the URL of the main menu item to the first form.
            $item = $navigationManager->getMainMenuItem('OFFLINE.Forms', 'main-menu-item');
            if ($item && $firstForm = $forms->first()) {
                $item->url = Backend::url('offline/forms/forms/submissions/' . $firstForm->id);
            }

            $forms->each(function (Form $form) use ($navigationManager) {
                $navigationManager->addSideMenuItems('OFFLINE.Forms', 'main-menu-item', [
                    'submissions-' . $form->id => [
                        'label' => $form->name,
                        'icon' => 'icon-list',
                        'permissions' => ['offline.forms::can_see_forms'],
                        'url' => Backend::url('offline/forms/submissions/index/' . $form->id),
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
    }

    /**
     * registerSettings used by the backend.
     */
    public function registerSettings()
    {
    }
}
