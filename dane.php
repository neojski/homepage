<?php
/*
http://www.crockford.com/javascript/private.html //ciekawie o js

http://www.crockford.com/javascript/private.html //ciekawie o js

http://img47.imageshack.us/img47/7664/83549124351153490650kx4.png

http://www.lucazappa.com/brilliantMaker/buttonImage.php

neo.mlodzi@gmail.com
tkolodziejski@gmail.com
http://reg.imageshack.us/setlogin.php?login=773412bee55b8cc5774efb8db3ad2c1e

http://www.mozillapl.org/katalogi_i_bazy/baza_dodatkow/rozszerzenia/dla_tworcow_stron/live_http_headers

webdevout.net

$sql=new mysqli('mysql.forall.pl','web162','haslo','usr_web162_2');
Bookmark page
http://altkomp.pl/modules/mod_bookmarkus/bookmark_us.js

<script src="http://www.google-analytics.com/urchin.js" type="text/javascript"></script>
<script type="text/javascript">_uacct = "UA-441633-1";urchinTracker();</script>
*/

if($_POST['submit']){
  if($_POST['password']=='oko007__'){
    $dane=file_get_contents('dane.php');
    $dane=str_replace('<?php'."\n".'/*','',$dane);
    $dane='<?php'."\n".'/*'."\n".$_POST['tekst']."\n".$dane;
    file_put_contents('dane.php',$dane);
    echo 'Udalo sie';
  }else{
    echo 'Operacja niemozliwa';
  }
}
?>
<form method="post" action="dane.php">
<textarea name="tekst" cols="50" rows="15"></textarea>
<input type="password" name="password">
<input type="submit" name="submit">
</form>
