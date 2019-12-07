<?php

function array_merge_real()
{
    $output = [];
    foreach (func_get_args() as $array) {
        foreach ($array as $key => $value) {
            $output[$key] = isset($output[$key]) ?
        array_merge($output[$key], $value) : $value;
        }
    }

    return $output;
}
