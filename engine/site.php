<?php
include('parser.php');
include('sql.php');


$silnik=new engine('t');
$silnik->xhtml=0;//uruchom html

print_r($_GET);
$c=explode('/',$_GET['c']);
$od=0;

if(preg_match('#^[0-9]{1,2}$#',$c[2])){
    $dzien=$c[2];
}
if(preg_match('#^[0-9]{1,2}$#',$c[1])){
    $miesiac=$c[1];
    if(preg_match('#^site[0-9]$#',$c[2])){
        $od=substr($c[2],4);
    }
}
if(preg_match('#^20[0-9][0-9]$#',$c[0])){
    $rok=$c[0];
    if(preg_match('#^site[0-9]$#',$c[1])){
        $od=substr($c[1],4);
    }
}
$temat=$c[3];


echo 'od'.$od;
echo 'temat '.$temat;

if($rok&&!$miesiac&&!$dzien){
    $result=$sql->query('
        SELECT a.nazwa, t.tresc, GROUP_CONCAT( DISTINCT d.nazwa
        SEPARATOR "\t" ) , a.data
        FROM dzialy d, zlacz_artykuly za, artykuly a, zlacz_teksty zt, teksty t
        WHERE za.dzial_id = d.id
        AND a.id = za.artykul_id
        AND a.id = zt.artykul_id
        AND t.id = zt.tekst_id
        GROUP BY za.artykul_id
        LIMIT '.$od.',1'
    );
    $all=array();
    while($row=$result->fetch_row()){
        echo $row[1];
        
        $dzial=explode("\t",$row[2]);
        $dzial_a=array();
        foreach($dzial as $v){
            $dzial_a[]=array('tresc'=>$v,'href'=>'/'.$v);
        }
        
        $all[]=array('nazwa'=>$row[0],'tresc'=>$row[1],'dzial'=>$dzial_a);
    }   
    $silnik->assign('tablica',$all);
}

$silnik->display();
?>
