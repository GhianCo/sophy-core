<?php

function snake_case(string $str): string {
    $snake_cased = [];
    $skip = [' ', '-', '_', '/', '\\', '|', ',', '.', ';', ':'];
    $len = strlen($str);
    $i = 0;

    while ($i < $len) {
        $last = count($snake_cased) > 0
            ? $snake_cased[count($snake_cased) - 1]
            : null;
        $character = $str[$i++];
        if (ctype_upper($character)) {
            if ($last !== '_') {
                $snake_cased[] = '_';
            }
            $snake_cased[] = strtolower($character);
        } elseif (ctype_lower($character)) {
            $snake_cased[] = $character;
        } elseif (in_array($character, $skip, true)) {
            if ($last !== '_') {
                $snake_cased[] = '_';
            }
            while ($i < $len && in_array($str[$i], $skip, true)) {
                $i++;
            }
        }
    }

    if (empty($snake_cased)) {
        return '';
    }

    if ($snake_cased[0] === '_') {
        $snake_cased[0] = '';
    }

    if ($snake_cased[count($snake_cased) - 1] === '_') {
        $snake_cased[count($snake_cased) - 1] = '';
    }

    return implode($snake_cased);
}
