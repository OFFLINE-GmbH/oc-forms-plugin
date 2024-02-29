    <?php
    /** @var \October\Rain\Database\Collection $value */
    if (is_array($value) && count($value)) {
        $value = collect($value)->pluck('value')->toArray();
        echo e(implode(', ', $value));
    }
