<?php

function type($mime, $default = null) {
    switch (strtolower($mime)) {
        case 'image/jpg':
        case 'image/png':
        case 'image/tif':
        case 'image/gif':
        case 'image/jpeg':
            return 'image';
            break;
        case 'application/pdf':
        case 'application/tif':
        case 'application/tiff':
            return 'pdf';
            break;
        case 'application/msword':
            return 'word';
            break;
        default:
            if ($default)
                return $default;
            return $mime;
    }
}

function sp2us($string) {
    return str_replace([" ", "\t"], "_", $string);
}
