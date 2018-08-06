<?php
        function unichr($input) {
            $dec = hexdec( $input[1] );
            
            if ($dec < 128) { 
                $utf = chr($dec); 
            } elseif ($dec < 2048) { 
                $utf = chr(192 + (($dec - ($dec % 64)) / 64)); 
                $utf .= chr(128 + ($dec % 64)); 
            } else { 
                $utf = chr(224 + (($dec - ($dec % 4096)) / 4096)); 
                $utf .= chr(128 + ((($dec % 4096) - ($dec % 64)) / 64)); 
                $utf .= chr(128 + ($dec % 64)); 
            }
            return $utf;
        }
        function encdec( $s ){
                return preg_replace_callback( '/&#x([a-f0-9]+);/iU', 'unichr', $s );
        }
if($txt=($_POST['tekst']=stripslashes($_POST['tekst']))){
  $sc=curl_init('http://www.google.com/tbproxy/spell?lang=pl&hl=pl');
  curl_setopt( $sc, CURLOPT_POST, TRUE );
  curl_setopt( $sc, CURLOPT_POSTFIELDS, '<spellrequest textalreadyclipped="0" ignoredups="0" ignoredigits="1" ignoreallcaps="1"><text>'.(htmlspecialchars($txt)).'</text></spellrequest>');
  ob_start();
  curl_exec($sc);
  $ret = ( ob_get_contents() );
  ob_end_clean();
  echo encdec($ret);
}
?>
