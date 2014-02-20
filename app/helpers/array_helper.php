<?php

function key_or_default($array, $key, $default = null) {
    if (key_exists($key, $array))
        return $array[$key];
    else
        return $default;
}
