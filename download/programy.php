<?php
#autorem skryptu jest neo
#http://www.neo.mlodzi.pl
#30.12.2005
if($_GET['password']=='tutaj_jest_bbardzo_skomplikowany_password,wiec nie zgadniesz'){/*aby dostać się do panelu admina wpisz po jego 
                                 właściwej nazwie ?password=hasło
															   aby zwiększyć, a włąsciwie w ogóle zabezpieczyć ten plik zmień wyraz hasło
															   występujący po w cudzysłowie na inny, bardziej skomplikowany*/
if($_POST['submit']){
$temp=$_POST['a'];
$dane='';
for($i=0;$i<count($temp['title']);$i++){
  if(strlen($temp['title'][$i])>0&&strlen($temp['url'][$i])>0){//jeśli podano dane
    $dane[$i]['title']=$temp['title'][$i];
    $dane[$i]['description']=$temp['description'][$i];
    $dane[$i]['url']=urldecode($temp['url'][$i]);
    $dane[$i]['ip']=$temp['ip'][$i];
    $dane[$i]['count']=strlen($temp['count'][$i])>0?$temp['count'][$i]:0;
  }
}
$file=fopen('dane.php','w');//otworz plik
fwrite($file,serialize($dane));//zapisz dane
fclose($file);//zapisz plik
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
  <meta http-equiv="content-type" content="text/html;charset=utf-8" />
  <meta name="Author" CONTENT="neo" />
  <title></title>
	<script type="text/javascript">
	function dodaj(){
	  arr=['title','descrition','url','count','ip']
		tr=document.createElement('tr')
	  for(i=0;i<arr.length;i++){
	    el=document.createElement('input')
	    el.setAttribute('type','text')
	    el.setAttribute('name','a['+arr[i]+'][]')
	    td=document.createElement('td')
	    td.appendChild(el)
	    tr.appendChild(td)
	  }
	  document.getElementById('dodaj').appendChild(tr)
  	return false;
	}
	</script>
  </head>
  <body>
	  <div><p>Aby dodać nowe pliki kliknij przycisk dodaj i wpisz odpowiednie dane. Następnie użyj przycika wyślij.</p>
	  <p>Aby usunąć pliki usuń z formularza usuń jego nazwę i/lub ścieżkę.</p>
    <?php
    $dane=unserialize(file_get_contents('dane.php'));
    echo '<form aciton="'.$_SERVER['PHP_SELF'].'" method="post"><table id="dodaj"><tr><td>tytuł</td><td>opis</td><td>sciezka</td><td>ile razy klikniety</td><td>adresy ip ktore kliknely</td></tr>';
		if(is_array($dane))
		foreach($dane as $k=>$v){
     echo '<tr><td><input type="text" name="a[title][]" value="'.$v['title'].'" /></td>'.
       '<td><input type="text" name="a[description][]" value="'.$v['description'].'" /></td>'.
  		 '<td><input type="text" name="a[url][]" value="'.$v['url'].'" /></td>'.
  		 '<td><input type="text" name="a[count][]" value="'.$v['count'].'" /></td>'.
			 '<td><input type="text" name="a[ip][]" value="'.$v['ip'].'" /></td></tr>';
    }
    ?>
    </table>
    <button onclick="return dodaj()">dodaj</button>
    <input type="submit" value="wyslij" name="submit" />
  </form>
  </div>
  </body>
</html>
<?php } ?>
