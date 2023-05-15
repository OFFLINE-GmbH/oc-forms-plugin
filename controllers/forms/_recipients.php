<?=/** @var $value array */ e(collect($value)->pluck('email')->join(', '));
