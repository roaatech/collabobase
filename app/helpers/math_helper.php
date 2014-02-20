<?php

function int_in_range($val = null, $min = null, $max = null, $default = null) {
    if (empty($val))
        return $default;
    $val = intval($val);
    if (!empty($min) && $val < intval($min))
        $val = $min;
    if (!empty($max) && $val > intval($max))
        $val = $max;
    return $val;
}
