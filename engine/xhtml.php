<?php
function xhtml($r=1){
    $xhtml = false;
    if (preg_match('/application\/xhtml\+xml(?![+a-z])(;q=(0\.\d{1,3}|[01]))?/i', $_SERVER['HTTP_ACCEPT'], $matches)) {
       $xhtmlQ = isset($matches[2])?($matches[2]+0.2):1;
       if (preg_match('/text\/html(;q=(0\d{1,3}|[01]))s?/i', $_SERVER['HTTP_ACCEPT'], $matches)) {
           $htmlQ = isset($matches[2]) ? $matches[2] : 1;
           $xhtml = ($xhtmlQ >= $htmlQ);
       } else {
           $xhtml = true;
       }
    }
    if ($xhtml && $r) {
       header('Content-Type: application/xhtml+xml; charset=utf-8');
    } else {
       header('Content-Type: text/html; charset=utf-8');
    }
}
?>
