<?php
die();
?>
SELECT GROUP_CONCAT( d.nazwa ) , za.artykul_id
FROM dzialy d, zlacz_artykuly za
WHERE za.dzial_id = d.id
GROUP BY za.artykul_id
LIMIT 0 , 30

/* arytkul, dzial, data */
SELECT a.nazwa, GROUP_CONCAT( d.nazwa
SEPARATOR "\t" ) , a.data
FROM dzialy d, zlacz_artykuly za, artykuly a
WHERE za.dzial_id = d.id
AND a.id = za.artykul_id
GROUP BY za.artykul_id
LIMIT 0 , 30

/* artykul, 1 tekst, dzial, data */
SELECT a.nazwa, t.tresc, GROUP_CONCAT( DISTINCT d.nazwa
SEPARATOR "\t" ) , a.data
FROM dzialy d, zlacz_artykuly za, artykuly a, zlacz_teksty zt, teksty t
WHERE za.dzial_id = d.id
AND a.id = za.artykul_id
AND a.id = zt.artykul_id
AND t.id = zt.tekst_id
GROUP BY za.artykul_id

/* tekst, typ tekstu; wymaga podania id_artykulu */
SELECT t.tresc, ty.nazwa
FROM teksty t, typy ty, zlacz_teksty zt
WHERE t.id = zt.tekst_id
AND ty.id = zt.typ_id
AND zt.artykul_id =1
ORDER BY zt.id DESC 

/* usuń polskie znaki */
replace( replace( replace( replace( replace( replace( replace( replace( replace( replace( replace( replace( lower( 'Zażółcić gęślą jaźń' ) , 'ą', 'a' ) , 'ć', 'c' ) , 'ę', 'e' ) , 'ł', 'l' ) , 'ń', 'n' ) , 'ó', 'o' ) , 'ś', 's' ) , 'ż', 'z' ) , 'ź', 'z' ) , ' ', '-' ) , '!', '-' ) , '?', '-' )  
