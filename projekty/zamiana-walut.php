<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pl">
  <head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8" />
    <title>neo homepage - przeliczanie walut, pobieranie kursu z NBP</title>
    <meta name="description" content="Szybkie, proste i zawsze aktualne przeliczanie walut. Kursy pobierane prosto z NBP." />
    <meta name="keywords" content="neo, php, przeliczanie walut, waluty, zamiana, pieniądze" /> 
<?php
#kursy walut 0.2
#created 5.01.2006
#modified 14.03.2006
#author neo - http://www.neo.mlodzi.pl
$file = 'http://www.nbp.pl/Kursy/xml/a004z060105.xml';//dziekuje nbp za udostepnianie danych
$xml = simplexml_load_file($file);//funkcja tylko php5!
$array=array('PLK'=>array('name'=>'polski złoty','count'=>1,'code'=>'PLK'));
$walutyf=$walutyt='<option value="PLK">polski złoty</option>';
foreach($xml->pozycja as $k => $v){
      $key=(string)$v->kod_waluty;
      $array[$key]['name']=(string)$v->nazwa_waluty;
      $array[$key]['code']=(string)$v->kod_waluty;
	    $array[$key]['count']=(float)str_replace(',','.',$v->kurs_sredni);    
      $walutyf.='<option value="'.$array[$key]['code'].'"'.(($_GET['f']==$array[$key]['code'])?' selected="selected"':'').'>'.$array[$key]['name'].'</option>';
      $walutyt.='<option value="'.$array[$key]['code'].'"'.(($_GET['t']==$array[$key]['code'])?' selected="selected"':'').'>'.$array[$key]['name'].'</option>';
}
if(($f=$_GET['f'])&&($t=$_GET['t'])&&($c=$_GET['c'])){
  $end=number_format($c/$array[$t]['count']*$array[$f]['count'],2).' '.$t;
}
?>
  </head>
  <body>
    <h1>Zamiana walut</h1>
    <h2>Aby przeliczyć pieniądze należy wybrać odpowiednie waluty i wpisać kwotę, zatwierdzając przyciskiem przelicz</h2>
    <form action="<?=$_SERVER['PHP_SELF']?>" method="get">
      <dl>
        <dt>Ilosc pieniedzy:</dt><dd><input type="text" name="c" value="<?=$_GET['c'];?>" /></dd>
        <dt>Waluta bierzaca:</dt><dd><select name="f"><?=$walutyf;?></select></dd>
        <dt>Waluta docelowa:</dt><dd><select name="t"><?=$walutyt;?></select></dd>
      </dl>
      <p><input type="submit" value="Przelicz" /></p>
      <h3>Wynik: <?=$end;?></h3>
    </form>
  </body>
</html>
