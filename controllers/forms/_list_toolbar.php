<div data-control="toolbar">
    <a
        href="<?= Backend::url('offline/forms/forms/create'); ?>"
        class="btn btn-primary oc-icon-plus">
        <?= e(trans('backend::lang.form.create')); ?>
    </a>
    <button
        class="btn btn-default oc-icon-trash-o"
        data-request="onDelete"
        data-request-confirm="<?= e(trans('backend::lang.list.delete_selected_confirm')); ?>"
        data-list-checked-trigger
        data-list-checked-request
        data-stripe-load-indicator>
        <?= e(trans('backend::lang.list.delete_selected')); ?>
    </button>

    <button
        class="btn btn-default oc-icon-copy"
        disabled="disabled"
        onclick="$(this).data('request-data', {
              checked: $('.control-list').listWidget('getChecked')
          })"
        data-request="onDuplicate"
        data-trigger-action="enable"
        data-trigger=".control-list input[type=checkbox]"
        data-trigger-condition="checked"
        data-request-success="$(this).prop('disabled', true)"
        data-stripe-load-indicator>
        <?= __("Duplicate") ?>
    </button>
</div>
