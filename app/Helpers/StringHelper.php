<?php

if (! function_exists('clean')) {

    function clean(string|array $params): array|string
    {
        return gettype($params) === 'array'
            ? array_map(fn (mixed $field): string => trim(strip_tags($field)), $params)
            : trim(strip_tags($params));
    }
}

if (! function_exists('isEmail')) {

    function isEmail(string $email): bool
    {
        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}
