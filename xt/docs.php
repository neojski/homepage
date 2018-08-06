<?php

include('xt.class.php');

$main=new xt('templates/main.html');

$fragment=$main->fragment('templates/docs.html');

	$szablon=htmlspecialchars(file_get_contents('templates/szablon.html'));

	$fragment->add('#szablon', $szablon);
	
	$szablon=str_replace(array('&lt;','&gt;'), array('<','>'), $szablon);
	
	foreach($fragment->getElementsByClassName('php') as $php_node){
		$php=trim($php_node->nodeValue);

		ob_start();
		eval(str_replace(array('include(\'xt.class.php\');', 'display()', 'szablon.html'), array('','display(1)','templates/szablon.html'),$php));
		$content=ob_get_contents();
		ob_end_clean();
		
		$php=preg_replace('#^(?!\s+$).*$#m', '<li><code>$0</code></li>', $php);
		
		$php='<ol>'.$php.'</ol>';
		
		$fragment->insertBefore($php, $php_node);
		
		$fragment->insertBefore($content, $php_node);
		
		$fragment->remove($php_node);	
	}

$main->add('#content', $fragment);
$main->display();

?>
