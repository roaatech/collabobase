<?php

/**
 * @param type $var the variable
 * @param string $functions a csv string contains names of functions to be applied from left to right
 * @return string
 */
function pass($var, $functions) {
    foreach (explode(",", $functions) as $function) {
        if ($function) {
            $var = $function($var);
        }
    }
    return $var;
}

function csv_ucfirst($csv, $glue = ", ") {
    return implode($glue, array_map(function($var) {
                return pass($var, "strtolower,ucfirst");
            }, explode(",", $csv)));
}

function labelize($var) {
    return csv_ucfirst(str_replace(array("_", ",", "-", " "), ",", $var), " ");
}

function nvl(&$var, $default = null) {
    return isset($var) ? $var : $default;
}

function html_entity_encode($string) {
//    $string = htmlentities($string, ENT_QUOTES);
    $chars = [
        "<" => "&lt;",
        ">" => "&gt;",
//        "&" => "&amp;",
        "¢" => "&cent;",
        "£" => "&pound;",
        "¥" => "&yen;",
        "€" => "&euro;",
        "§" => "&sect;",
        "©" => "&copy;",
        "®" => "&reg;",
        "™" => "&trade;",
        '"' => "&quot;",
        "\'" => "&apos;",
    ];
    $string = str_replace("&", "&amp;", $string);
    $string = str_replace(array_keys($chars), array_values($chars), $string);
    return $string;
}

function get_snippet($str, $wordCount = 10) {
    return implode(
            '', array_slice(
                    preg_split(
                            '/([\s,\.;\?\!]+)/', $str, $wordCount * 2 + 1, PREG_SPLIT_DELIM_CAPTURE
                    ), 0, $wordCount * 2 - 1
            )
    );
}
