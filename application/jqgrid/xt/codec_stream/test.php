<?php

$pattern = '/^(([\d]*)h)?\s*(([\d]*)mn)?\s*(([\d]*)s)?\s*(([\d]*)ms)?/';
$str = '1h 30mn 5s 344ms';
$str = '5s 344ms';

preg_match($pattern, $str, $matches);
print_r($matches);
$s = sprintf("%02d:%02d:%02d.%03d", $matches[2], $matches[4], $matches[6], $matches[8]);
print_r($s);
?>