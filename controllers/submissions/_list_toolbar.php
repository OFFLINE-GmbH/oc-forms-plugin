<div data-control="toolbar">
    <a
        href="<?= Backend::url('offline/forms/submissions/create/' . ($this->params[0] ?? -1)) ?>"
        class="btn btn-primary oc-icon-plus">
        <?= e(trans('backend::lang.form.create')) ?>
    </a>
    <a
        href="<?= Backend::url('offline/forms/submissions/export/' . ($this->params[0] ?? -1)) ?>"
        class="btn btn-default oc-icon-download">
        <?= e(trans('offline.forms::lang.export_submissions')) ?>
    </a>
    <button
        class="btn btn-default oc-icon-trash-o"
        data-request="onDelete"
        data-request-confirm="<?= e(trans('backend::lang.list.delete_selected_confirm')) ?>"
        data-list-checked-trigger
        data-list-checked-request
        data-stripe-load-indicator>
        <?= e(trans('backend::lang.list.delete_selected')) ?>
    </button>
</div>
