<?php

function internal_redirect($controller = "home", $method = "", $others = "", $query = null) {
    $url = $controller;
    if ($method)
        $url.="/$method";
    if ($others)
        $url.="/$others";
    if (is_array($query))
        $url.=http_build_query($query);
    $url = base_url($url);
    redirect($url, 'location');
}
