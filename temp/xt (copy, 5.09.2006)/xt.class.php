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
		$this->removeCDATAs();
		$this->xml=new DOMDocument();
		$this->xml->loadHTML($this->template); //musi być html, bo niezamknięcie czegoś skończyłoby się porażką!
		$this->body=$this->xml->getElementsByTagName('body')->item(0);
		$this->head=$this->xml->getElementsByTagName('head')->item(0);
		$this->html=$this->xml->documentElement;
		$this->xml->formatOutput=true;
		$this->classes=array();//dla getElementByClassname
		$this->tags=array();//dla getElementByTagName
	}
	public function __destruct(){
		//musi być xml, jeśli chcemy xhtml!!!
		//echo '<pre>'.(htmlspecialchars($this->comment_cdatas($this->xml->saveXML()))).'</pre>';
		echo $this->comment_cdatas($this->xml->saveXML());
	}
	public function error($str){
		die('<strong>'.$str.'</strong>');
	}
	private function comment_cdatas($str){
		return str_replace(array('<![CDATA[', ']]>'), array('/*<![CDATA[*/', '/*]]>*/'), $str);
	}
	private function removecdatas(){
		$this->template=str_replace(array('<![CDATA[', ']]>'), '', $this->template); //remove
		$this->template=preg_replace('#^(.*?)//\s*$#m', '\1', $this->template);  //delete empty inline comments
		$this->template=preg_replace('#/\*\s*\*/#s', '', $this->template); //delete empty multiline comments
	}
	/**
	 * poprawny szablon:
	 * opcjonalnie nagłówek xml
	 * DOCTYPE z odpowiednią wielkością liter
	 * elementy html, head, title, body
	 **/
	private function validateTemplate(){
		return preg_match('#(<\?xml[^>]*?>)?\s*<!DOCTYPE[^>]*(<.*?>)*[^>]*>\s*<html[^>]*?>(?>.*?<head[^>]*?>)(?>.*?<title>)(?>.*?</title>)(?>.*?</head>)(?>.*?<body[^>]*?>)(?>.*?</body>).*?</html>#s', $this->template);
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
		}elseif($node=$this->getElementById($name)){
			return $node;
		}elseif($node=$this->getElementByClassName($name)){
			return $node;
		}else{
			$this->error(is_string($name)?'Obiekt o  id/klasie"'.$name.'" nie istnieje':'Obiekt domnode nie istnieje');
			return null;
		}
	}
	
	/**
	 * zwraca kolejny tag element o podanej nazwie tagu
	 */
	public function getElementByTagName($tag, $node=0){
		if(!$node){
			$node=$this->html;
		}
		if($this->tags[$tag]!==$node->getElementsByTagName($tag) || !isset($this->tags[$tag])){
			$this->tags[$tag]=array($node->getElementsByTagName($tag), 0);
		}
		return $this->tags[$tag][0]->item($this->tags[$tag][1]++);
	}
	/** 
	 * działa dobrze, ale powinno zwracać domnodelist
	 * @param domnode
	 * @param string className
	 */
	public function getElementsByClassName($class, $node=0){
		if(!$node){
			$node=$this->html;
		}
		if(is_node($node)){
			$return=array();
			foreach($node->getElementsByTagName('*') as $tag){
				if(preg_match('#\b'.$class.'\b#', $tag->getAttribute('class'))){
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
	public function getElementByClassName($class, $node=0){
		if(!$node){
			$node=$this->html;
		}
		if($this->classes[$class]!==$this->getElementsByClassName($class, $node) || !isset($this->classes[$class])){
			$this->classes[$class]=$this->getElementsByClassName($class, $node);
		}
		$return=current($this->classes[$class]);
		next($this->classes[$class]);
		return $return;
	}
	/**
	 * odpowiednik dom, ważnym elementem parametr 1 czyli rodzic! główna różnica domowego
	 */
	public function getElementById($id, $node=0){
		if(!$node){
			$node=$this->html;
		}
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
				if(is_node($this->getElementById($key, $clone))){
					$this->add($this->getElementById($key, $clone), $value);
					$this->getElementById($key, $clone)->removeAttribute('id');
				}else{
					//$this->error('Nie mam obiektu o id '.$key);
				}
			}
			$node->parentNode->insertBefore($clone, $node);
		}
		$node->parentNode->removeChild($node);
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
	
	public function cssFile($url, $title=false, $media=false){
		$this->link($url, 'stylesheet', $title, 'text/css', $media);
	}
	
	public function jsFile($url, $alternate_code=null){
		$this->head->appendChild($this->create('script', $alternate_code, array('type'=>'text/javascript','src'=>$url)));
	}

	/**
	 * @param str css-input
	 * @param bool dodać-nowy-tag-style
	 */
	public function css($str, $new=0){
		if($new){
			$this->head->appendChild($this->create('style', '<![CDATA['. $str .']]>', array('type'=>'text/css')));
		}else{
			if($style=$this->getElementByTagName('style', $this->head)){
				$style->firstChild->data.=$str;
			}else{
				$this->css($str, 1);
			}
		}
	}
	
	/**
	 * @param str kod_javascript
	 * @param bool dodac_nowy_znacznik
	 */
	public function js($str, $new=0){
		if($new){
			$this->head->appendChild($this->create('script', '<![CDATA['. $str .']]>', array('type'=>'text/javascript')));
		}else{
			if($script=$this->getElementByTagName('script', $this->head)){
				$script->firstChild->data.=$str;
			}else{
				$this->js($str, 1);
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
}
?>