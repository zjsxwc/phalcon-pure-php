<?php
namespace Phalcon;

/**
 * Filter alphanum string
 *
 * @param string $param            
 * @return string
 */
function filter_alphanum($param)
{
    $param .= '';
    $filtered_str = '';
    $len = strlen($param);
    for ($i = 0; $i < $len; ++ $i) {
        $ch = ord($param[$i]);
        if (($ch >= 97 && $ch <= 122) || ($ch >= 65 && $ch <= 90)) {
            $filtered_str .= chr($ch);
        }
    }
    return $filtered_str;
}

function starts_with($str, $start, $ignoreCase = null)
{
    return ($ignoreCase ? strcasecmp($start, substr($str, 0, strlen($start))) : strcmp($start, substr($str, 0, strlen($start)))) == 0;
}

function ends_with($str, $end, $ignoreCase = null)
{
    return ($ignoreCase ? strcasecmp($end, substr($str, - strlen($end))) : strcmp($end, substr($str, - strlen($end)))) == 0;
}

/**
 *
 * @param string $param            
 * @return string
 */
function is_basic_charset($param)
{
    $len = strlen($param);
    $iso88591 = false;
    for ($i = 0; $i < $len; ++ $i) {
        $ch = ord($param[$i]);
        if ($ch == 172 || ($ch >= 128 && $ch <= 159)) {
            continue;
        }
        if ($ch >= 160 && $ch <= 255) {
            $iso88591 = true;
            continue;
        }
        return false;
    }
    if (! $iso88591) {
        return 'ASCII';
    }
    return 'ISO-8859-1';
}