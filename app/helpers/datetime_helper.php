<?php

function dt($date, $format = 'Y-m-d H:i:s', $default = null, $timezone = null) {
    if (empty($date))
        return $default;
    if (is_string($date))
        $date = strtotime($date);

    $date = new DateTime(date("Y-m-d H:i:s", $date));
    if ($timezone !== null) {
        $tz = new DateTimezone($timezone);
        $date->setTimezone($tz);
    }
    $formatted = $date->format($format);
    return $formatted;
}

function is_date_or_null($date) {
    if (!empty($date) && !strtotime($date))
        return false;
    return true;
}

function date_human($date, $timezone = null, $format = "j (F) Y, h:i (a)", $formatNoYear = "j (F), h:i (a)", $addPrePrepositions = true) {

    if (!$timezone) {
        $timezone = CurrentUser::timezone();
    }
    if (!$timezone) {
        $timezone = "Asia/Dubai";
    }

    if (!is_numeric($date)) {
        $date = strtotime($date);
    }

    if (!$date) {
        return null;
    }

    $now = time();
    $dif = $now - $date;

    $tz = new DateTimezone($timezone);
    $date = new DateTime(date("Y-m-d H:i:s", $date));
    $date->setTimezone($tz);
    $formatted = ($addPrePrepositions ? __("on") . " " : "") . $date->format($format);
    $count = 1;
    while ($count > 0) {
        $formatted = preg_replace_callback("#^(.*)\((.*)\){1}?(.*)$#", function($matches) {
            return $matches[1] . __($matches[2]) . $matches[3];
        }, $formatted, -1, $count);
    }
    $formattedNoYear = ($addPrePrepositions ? __("on") . " " : "") . $date->format($formatNoYear);
    $count = 1;
    while ($count > 0) {
        $formattedNoYear = preg_replace_callback("#^(.*)\((.*)\){1}?(.*)$#", function($matches) {
            return $matches[1] . __($matches[2]) . $matches[3];
        }, $formattedNoYear, -1, $count);
    }

    $year = $date->format('Y');
    $month = $date->format('m');
    $day = $date->format('d');
    $dayT = $date->format('l');
    $hour = $date->format('h');
    $minute = $date->format('i');
    $period = $date->format('a');

    $today = date("d");
    $thisYear = date("Y");

    if ($dif < 0) {
        if ($dif > -60) { //seconds
            $plural = $dif > 1 ? "s" : "";
            $result = "$dif " . __("second$plural later");
        } elseif ($dif > -60 * 60) { //minutes
            $val = abs(floor($dif / 60));
            $plural = $val > 1 ? "s" : "";
            $result = "$val " . __("minute$plural later");
        } elseif ($day != $today && $dif > -60 * 60 * 24) {
            $result = __("Tomorrow at") . " $hour:$minute " . __($period);
        } elseif ($dif > -60 * 60 * 24) {
            $val = abs(floor($dif / 3600));
            $plural = $val > 1 ? "s" : "";
            $val2 = abs(floor(($dif - $val * 60 * 60) / 60));
            $plural2 = $val2 > 1 ? "s" : "";
            $result = "$val " . __("hour$plural") . ($val2 > 0 ? __("and") . $val2 . __("minute$plural2") : "") . " " . __("later");
        } elseif ($dif > -60 * 60 * 24 * 7) {
            $result = __("next $dayT at") . " $hour:$minute " . __($period);
        } elseif ($year == $thisYear) {
            $result = $formattedNoYear;
        } else {
            $result = $formatted;
        }
    } elseif ($dif > 0) {
        if ($dif < 60) { //seconds
            $plural = $dif > 1 ? "s" : "";
            $result = "$dif " . __("second$plural ago");
        } elseif ($dif < 60 * 60) { //minutes
            $val = abs(floor($dif / 60));
            $plural = $val > 1 ? "s" : "";
            $result = "$val " . __("minute$plural ago");
        } elseif ($day != $today && $dif < 60 * 60 * 24) {
            $result = __("Yesterday at") . " $hour:$minute " . __($period);
        } elseif ($dif < 60 * 60 * 24) {
            $val = abs(floor($dif / 3600));
            $plural = $val > 1 ? "s" : "";
            $val2 = abs(floor(($dif - $val * 60 * 60) / 60));
            $plural2 = $val2 > 1 ? "s" : "";
            $result = "$val " . __("hour$plural") . " " . ($val2 > 0 ? __("and") . " $val2 " . __("minute$plural2") : "") . " " . __("ago");
        } elseif ($dif < 60 * 60 * 24 * 7) {
            $result = __("last $dayT at") . " $hour:$minute " . __($period);
        } elseif ($year == $thisYear) {
            $result = $formattedNoYear;
        } else {
            $result = $formatted;
        }
    } else {
        $result = "right now";
    }
    return $result;
}
