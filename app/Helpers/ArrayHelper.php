<?php

if (! function_exists('removeEmpty')) {

    function removeEmpty(array $param): array
    {
        return array_filter($param, fn (mixed $value): bool => ! empty($value));
    }
}
