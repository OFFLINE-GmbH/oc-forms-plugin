<div
    style="
        max-width: 350px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    ">
    <?php
    /** @var October\Rain\Database\Collection $value */
    foreach ($value as $file):
        ?>
        <a href="<?= e($file->getPath()); ?>" target="_blank">
            <?php if ($file->isImage()): ?>
                <img src="<?= e($file->getThumb(40, 40)); ?>" alt="<?= e($file->file_name); ?>">
            <?php else: ?>
                <div
                    style="
                    max-width: 150px;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                    background: var(--bs-secondary-bg-subtle);
                    border-radius: var(--bs-border-radius);
                    font-size: .9rem;
                    padding: .3rem .6rem;
                    display: inline-flex;
                    align-items: center;
                ">
                    <span class="oc-icon-download"></span>
                    <?= e($file->file_name); ?>
                </div>
            <?php endif; ?>
        </a>
    <?php endforeach; ?>
</div>
