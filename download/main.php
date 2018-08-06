<?php
#autorem skryptu jest neo
#http://www.neo.mlodzi.pl
#30.12.2005
if(isset($_GET['url'])){//jesli podano urla to znaczy ze dodajemy dane
  $url=urldecode($_GET['url']);
  $dane=unserialize(file_get_contents('dane.php'));//wczytanie danych  
  $ip=$_SERVER['HTTP_X_FORWARDED_FOR']?$_SERVER['HTTP_X_FORWARDED_FOR']:$_SERVER['REMOTE_ADDR'];//adres ip
  foreach($dane as $k => $v){  
    if($v['url']==$url){
      if(strpos($v['ip'],$ip)===false){//jesli z tego adresu jeszcze nie pobrano
	      $dane[$k]['count']++;//dodaj do licznika
  	    $dane[$k]['ip'].='|'.$ip;//dodaj ip
		    $file=fopen('dane.php','w');//otworz plik
        fwrite($file,serialize($dane));//zapisz dane
        fclose($file);//zapisz plik
        break;
      }
    }
	}
	header('Location:'.$url);
}elseif(isset($_GET['count'])){//wyswietl tablice wynikow
  $dane=unserialize(file_get_contents('dane.php'));//wczytaj dane  
  function cmp($a,$b){return $a['count']>$b['count']?-1:1;}//funkcja sortujaca
	usort($dane,'cmp');//sortuj	
	echo '<table><tr><td>tajtl</td><td>deskripszn</td><td>ile klik</td></tr>';//echowanie
	$count=$_GET['count']>0?$_GET['count']:strlen($dane);
	for($i=0;$i<$count;$i++){
	  if(strlen($dane[$i]['title'])>0)
	  echo '<tr><td><a href="'.$_SERVER['PHP_SELF'].'?url='.$dane[$i]['url'].'">'.$dane[$i]['title'].'</a></td><td>'.$dane[$i]['description'].'</td><td>'.$dane[$i]['count'].'</td></tr>';//echuje opis + ile razy
	}
	echo '</table>';//koniec echowania
}
?>
