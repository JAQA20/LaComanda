<?php

function app_normalize_text($text)
{
    if (!is_string($text)) {
        return $text;
    }

    $text = trim($text);
    if ($text === '') {
        return $text;
    }

    for ($i = 0; $i < 5; $i++) {
        if (preg_match('/Ã.|Â.|â./u', $text) !== 1 && preg_match('//u', $text) === 1) {
            break;
        }

        $converted = null;

        if (function_exists('iconv')) {
            $converted = @iconv('ISO-8859-1', 'UTF-8//IGNORE', $text);
            if (!is_string($converted) || $converted === '') {
                $converted = null;
            }
        }

        if ($converted === null) {
            $converted = @mb_convert_encoding($text, 'UTF-8', 'ISO-8859-1');
        }

        if (!is_string($converted) || $converted === '' || $converted === $text) {
            break;
        }

        $text = $converted;
    }

    return $text;
}

function app_normalize_order_array(array $orders): array
{
    foreach ($orders as &$order) {
        foreach ($order as $key => $value) {
            if (is_string($value)) {
                $order[$key] = app_normalize_text($value);
            }
        }
    }
    unset($order);

    return $orders;
}
