<?php 

/** <p>Base64のURLに使えない文字列を置換</p> */
function url64_encode($data){ return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); } 
/** <p>Base64のURLに使えない文字列を置換</p> */
function url64_decode($data){ return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT)); }