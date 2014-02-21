<?php

function __($line, $id = '') {
    static $store = array();
    $result = null;
    if (array_key_exists($line, $store)) {
        $result = $store[$line];
    }
    if (!$result) {
        $result = lang($line, $id);
    }
    if (!$result) {
        $result = $line;
        //addLangLine($line);
    }
    $store[$line] = $result;
    return $result;
}

function addLangLine($line, $lang = null) {
    if (!$lang) {
        $lang = MY_Controller::get_instance()->getLanguage();
    }
    $file = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "language" . DIRECTORY_SEPARATOR . $lang . DIRECTORY_SEPARATOR . "general_lang.php";
    $fh = fopen($file, 'a') or die("can't open the language file for write");
    $eline = str_replace('"', '\"', $line);
    $lineContent = '$lang["' . $eline . '"]="' . $eline . '";' . "\n";
    fwrite($fh, $lineContent);
    fclose($fh);
}
