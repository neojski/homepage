<?php
$file = 'adblock.txt';

if(isset($_POST['password']) && sha1($_POST['password']) === 'c49ef1d2f82d539f13bd2fbe426065f8ec9cc7da'){
	file_put_contents($file, stripslashes($_POST['filtr']));
	
	echo '<p>zaktualizowano</p>';
}
?>


<form method="post">
<ul>
<li><textarea name="filtr" cols="100" rows="20"><?php
echo file_get_contents('adblock.txt');
?>
</textarea></li>
<li><input type="password" name="password" /></li>
<li><input type="submit" /></li>
</ul>
</form>
