<?php
function is_node($node){
	ob_start();
	var_dump($node);
	$content=ob_get_contents();
	ob_end_clean();
	return substr($content, 0, 18)=='object(DOMElement)'?true:false;
}

define('XHTML','<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<title></title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	</head>
	<body>
	</body>
</html>');

class xt{
	public function __construct($file){
		if(is_file($file)){
			if(file_exists($file)){
				$this->template=file_get_contents($file);
			}else{
				$this->error('Template file '.$file.' not found.');
			}
		}else{
			$this->template=$file;
		}
		if(!$this->validateTemplate()){
			$this->error('Template file is not valid');
		}
		$this->xml=new DOMDocument();
		$this->xml->loadHTML($this->template);
		$this->body=$this->xml->getElementsByTagName('body')->item(0);
		$this->head=$this->xml->getElementsByTagname('head')->item(0);
		$this->xml->formatOutput=true;
		$this->classes=array();//dla getElementByClassname
	}
	public function __destruct(){
		echo $this->xml->savexml();
	}
	public function error($str){
		die('<strong>'.$str.'</strong>');
	}
	/**
	 * poprawny szablon:
	 * opcjonalnie nagłówek xml
	 * DOCTYPE z odpowiednią wielkością liter
	 * elementy html, head, title, body
	 **/
	private function validateTemplate(){
		return preg_match('#(<\?xml[^>]*?>)?\s*<!DOCTYPE\s+[a-z]+\s+[A-Z]+\s+".*?"\s+(?:".*?")>\s*<html[^>]*?>(?>.*?<head[^>]*?>)(?>.*?<title>)(?>.*?</title>)(?>.*?</head>)(?>.*?<body[^>]*?>)(?>.*?</body>).*?</html>#s', $this->template);
	}
	
	/* uniknąć literówek */
	public function __call($name, $arguments){
		if(!method_exists($this, $name)){
			$this->error('Metoda '.$name.' nie istnieje!');
		}
	}
	/**
	 * @param object domelement
	 * @param str append-text
	 */
	public function appendText($node, $str){
		if(is_node($node)){
			$node->appendChild($this->text2html($str));
		}
	}
	/**
	 * @param object domelement
	 * @param str replace-text
	 */
	public function replaceText($node, $str){
		if(is_node($node)){
			$this->appendText($node, $str);
			$this->xml->removeChild($node);
		}
	}
	
	/**
	 * @param str text
	 * @return object domelement
	 */
	public function text2html($str){
		$child=$this->xml->createDocumentFragment();
		$child->appendXML($str);
		return $child;
	}
	/**
	 * sprawdza, czy node jest dzieckiem głównego dokumentu
	 * @param object domelement
	 * @return bool
	 */
	public function checkNode($node){
		if(!is_node($node)){
			return false;
		}else{
			return $node->ownerDocument==$this->xml;
		}
	}
	/**
	 * @param object domnode / str id / str class
	 * @return null / object domnode
	 */ 
	public function getNode($name){
		if(is_node($name)){
			return $name;
		}elseif($node=$this->xml->getElementById($name)){
			return $node;
		}elseif($node=$this->getElementByClassName($this->xml->documentElement,$name)){
			return $node;
		}else{
			$this->error(is_string($name)?'Obiekt o  id/klasie"'.$name.'" nie istnieje':'Obiekt domnode nie istnieje');
			return null;
		}
	}
	/**
	 * nadawanie atrybutuów obiektowi
	 * arguemtny w tablicy lub jako kolejene parametry funkcji
	 */
	public function set($node){
		if($node=$this->getNode($node)){
			$arguments=func_get_args();
			if(is_array($arguments[1])){
				foreach($arguments[1] as $attribute => $value){
					if($value!==false){
						if($attribute!='data'){
							$node->setAttribute($attribute, $value);
						}else{
							$this->appendText($node, $value);
						}
					}
				}
			}else{
				for($i=1; $i<count($arguments)-1; $i+=2){
					if($arguments[$i+1]!==false){
						if($attribute!='data'){
							$node->setAttribute($arguments[$i], $arguments[$i+1]);
						}else{
							$this->appendText($node, $value);
						}
					}
				}
			}
		}
	}
	/**
	 * ustawienie stylow liniowych elementowi
	 */
	public function setStyle($node, $style){
		if($node=$this->getNode($node)){
			$node->setAttribute('style', $style);
		}
	}

	/** 
	 * działa dobrze, ale powinno zwracać domnodelist
	 * @param domnode
	 * @param string className
	 */
	public function getElementsByClassName($node, $class){
		if(is_node($node)){
			$return=array();
			foreach($node->getElementsByTagName('*') as $tag){
				if(preg_match('#(^| )'.$class.'($| )#', $tag->getAttribute('class'))){
					$return[]=$tag;
				}
			}
			return empty($return)?null:$return;
		}
	}
	/**
	 * pobiera kolejny element wg klasy
	 * jeśli tablica klasy zostanie zmodyfikowana - zaczyna od początku
	 */
	public function getElementByClassName($node, $class){
		if($this->classes[$class]!==$this->getElementsByClassName($node, $class) || !isset($this->classes[$class])){
			$this->classes[$class]=$this->getElementsByClassName($node, $class);
		}
		$return=current($this->classes[$class]);
		next($this->classes[$class]);
		return $return;
	}
	/**
	 * odpowiednik dom, ważnym elementem parametr 1 czyli rodzic! główna różnica domowego
	 */
	public function getElementById($node, $id){
		foreach($node->getElementsByTagName('*') as $tag){
			if($tag->getAttribute('id')==$id){
				return $tag;
			}
		}
		return null;
	}
	/**
	 * głowna funkcja dodająca wartości/parametry, obsługująca pętle
	 */
	public function add($name, $value){
		if($node=$this->getNode($name)){
			if(is_array($value) && is_array($value[0])){//czyli pętelka
				$node->removeAttribute('id');
				$this->r($node, $value);
			}elseif(is_array($value)){//nalezy skorzystać z set, bo mamy tablicę
				$this->set($node, $value);
			}elseif(is_string($value)){//zwykły ciąg, czyli najprostsza możliwość
				$this->appendText($node, $value);
			}elseif(is_node($value)){//albo obiekt
				$node->appendChild($value);
			}
		}
	}
	/**
	 * pomocnicza funkcja główej
	 */
	private function r($node, $all){
		foreach($all as $row){
			$clone=$node->cloneNode(true);
			foreach($row as $key => $value){
				$this->add($this->getElementById($clone,$key), $value);
				$this->getElementById($clone, $key)->removeAttribute('id');
			}
			$node->parentNode->insertBefore($clone, $node);
		}
		$node->parentNode->removeChild($node);
	}
	/**
	 * tworzenie elementow dom
	 */
	public function create($name, $str=0, $arguments=0){
		if(!$name){
			return null;
		}
		$node=$this->xml->createElement($name);
		if($str){
			$this->appendText($node, $str);
		}
		if($arguments){
			if(func_num_args()>3){
				$arguments=func_get_args();
				$arguments=array_slice($arguments, 2);
				for($i=0; $i<count($arguments); $i+=2){
					$this->set($node, $arguments[$i], $arguments[$i+1]);
				}
			}else{
				$this->set($node, $arguments);
			}
		}
		return $node;
	}
	
	public function link($url, $rel, $title=false, $type=false, $media=false){
		$link=$this->create('link', null, array('rel'=>$rel, 'href'=>$url, 'title'=>$title, 'type'=>$type, 'media'=>$media));
		$this->head->appendChild($link);
	}
	
	public function cssLink($url, $title=false, $media=false){
		$this->link($url, 'stylesheet', $title, 'text/css', $media);
	}
	
	public function getElementByTagName($node, $name){
		foreach($node->getElementsByTagName($name) as $element){
			return $element;
		}
	}
	/**
	 * @param str css-input
	 * @param bool dodać-nowy-tag-style
	 */
	public function css($str, $new=0){
		if($new){
			$this->head->appendChild($this->create('style', $str, array('type'=>'text/css')));
		}else{
			if($style=$this->getElementByTagName($this->head, 'style')){
				$style->appendChild($this->xml->createTextNode($str));
			}else{
				$this->css($str, 1);
			}
		}
	}
	
	public function js($str, $new=0){
		if($new){
			$this->head->appendChild($this->create('script', $str, array('type'=>'text/javascript')));
		}else{
			if($style=$this->getElementByTagName($this->head, 'script')){
				$style->appendChild($this->xml->createTextNode($str));
			}else{
				$this->js($str, 1);
			}
		}
	}
	
	function jsFile($url, $alternate_code=null){
		$this->head->appendChild($this->create('script', $alternate_code, array('type'=>'text/javascript','src'=>$url)));
	} 
}
?>