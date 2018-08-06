<?php
/*
 *	xt templates system
 *	Copyright :(C) 2007 Tomasz 'neo' Kołodziejski <tkolodziejski at gmail dot com>, <neo007 at jabber dot com>
 *	E-mail    :tkolodziejski@gmail.com
 *	Website   :http://neo.mlodzi.pl/xt
 *
 *	This library is free software; you can redistribute it and/or
 *	modify it under the terms of the GNU Lesser General Public
 *	License as published by the Free Software Foundation; either
 *	version 2.1 of the License, or (at your option) any later version.
 *	
 *	This library is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 *	Lesser General Public License for more details.
 *
 *	You should have received a copy of the GNU Lesser General Public
 *	License along with this library; if not, write to the Free Software
 *	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 */
// define constant variables
define('GETNODE_METHOD_XPATH', 1);
define('GETNODE_METHOD_CSS',2);

define('XHTML', 2);
define('HTML', 3);
define('XML', 4);
define('RSS', 5);
define('ATOM', 6);
class xt{
	private $core = array(
		'fragment',
		'switcher'
	);
	private $dir; // xt-core folder
	public $strict; // np. czy są entities
	/**
	 * constuction method
	 */
	public function __construct($file=0, $is_string=0){
		// entities, pobiera referencje
		require_once('entities-ref/entities.php');
		$this->entities_from = $entities_from;
		$this->entities_to = $entities_to;
		// koniec
	
		$this->dir = dirname(__FILE__);
		$this->find_plugins();
		$this->getnode_method = 2;
		$this->strict = false;
		if($file){
			$this->load($file, $is_string);
		}
		$this->view = new stdclass;
	}
	
	/**
	 * an easy method for finding plugins 
	 * in xt-plugins directory
	 */
	private function find_plugins(){
		$this->plugins = array();
		foreach(glob($this->dir.'/../xt-plugins/*') as $file){
			$this->plugins[] = substr(basename($file, '.php'), 3);
		}
	}
	
	/**
	 * include plugins, only when necessary
	 */
	public function __get($name){
		if(in_array($name, $this->plugins)){
			if(!isset($this->$name)){
				require_once($this->dir.'/../xt-plugins/xt.'.$name.'.php');
				$this->$name=new $name($this);
			}
		}
		if(in_array($name, $this->core)){
			if(!isset($this->$name)){
				require_once($this->dir.'/xt.'.$name.'.php');
				$this->$name=new $name($this);
			}
		}
		return $this->$name;
	}
	
	/**
	 * antyliterówka
	 */
	public function __call($name, $arguments){
		if(!method_exists($this, $name)){
			 //throw new xtException('Metoda '.$name.' nie istnieje!');
		}
	}
	
	/**
	 * @param str filename/template
	 */
	public $name;		// nazwa szablonu
	public $dom;		// obiekt xml szablonu
	public $template;	// zawartość szablonu
	public $root;		// głowny element
	public function load($file, $is_string=false){
		if(!$is_string){
			if(file_exists($file)){
				$this->template=file_get_contents($file);
			}else{
				throw new xtException('Plik szablonu <code>'.htmlspecialchars($file).'</code> nie istnieje');
			}
		}else{
			$this->template=$file;
		}
		$this->name=$file;
		$this->dom=new mydom();
		$this->check_encoding();
		if(!$this->strict){
			$this->entities();
		}
		$this->dom->loadxml($this->template);
		$this->root=$this->dom->documentElement;
		$this->dom->formatOutput=true;
		$this->useXML=$this->is_xml();
		$this->xpath=new DOMXPath($this->dom);
		$this->check_namespaces();
	}
	
	public function entities($str=null){
		if($str){
			return str_replace($this->entities_from, $this->entities_to, $str);
		}else{
			$this->template=str_replace($this->entities_from, $this->entities_to, $this->template);
		}
	}
	
	/**
	 * check encoding:
	 * add xml encoding prologue, which is necessary for loadxml
	 */
	private function check_encoding(){
		if(preg_match(
			'#<\?xml[^>]+encoding="([^"]+)"[^>]*?>#',
			$this->template,
			$encoding
		)){
			$this->encoding=$encoding[1];
		}elseif(preg_match(
			'#<meta[^>]+content="[^=]+=(.*?)"[^>]*>#s',
			$this->template,
			$encoding
		)){
			$this->template='<?xml version="1.0" encoding="'.$encoding[1].'"?>'.$this->template;
			$this->encoding=$encoding[1];
		}else{
			// wrzuć domyślne utf-8
			$this->template='<?xml version="1.0" encoding="utf-8"?>'.$this->template;
			$this->encoding='utf-8';
			//throw new xtException('Brak ustawionego kodowania!');
		}
	}
	
	/**
	 * find namespaces definitions
	 */
	private function check_namespaces(){
		preg_match_all('#xmlns:[a-z]+="[^"]+"#', $this->template, $match);
		$namespaces=$match[0];
		
		# register all namespaces
		foreach($namespaces as $string){
			preg_match('#xmlns:(.*?)=#', $string, $prefix);
			preg_match('#"(.*?)"#', $string, $uri);
			
			$this->xpath->registerNamespace($prefix[1], $uri[1]);
		}
		
		# register default namespace
		if(strlen($this->root->lookupNamespaceURI(null))>0){
			$this->xpath->defaultNamespace = true;
			$this->xpath->registerNamespace('default', $this->root->lookupNamespaceURI(null));
		}else{
			$this->xpath->defaultNamespace = false;
		}
		$this->namespaces=implode(' ', $namespaces);
	}
	
	/**
	 * rozpoznawanie czy przeglądarka obsługuje xhtml
	 * autorem jest dr-no http://www.doktorno.boo.pl/index.php?q=art008
	 */
	private function is_xml(){
		$xhtml = false;
		if(preg_match('/application\/xhtml\+xml(?![+a-z])(;q=(0\.\d{1,3}|[01]))?/i', $_SERVER['HTTP_ACCEPT'], $matches)){
			$xhtmlQ = isset($matches[2])?($matches[2]+0.2):1;
			if(preg_match('/text\/html(;q=(0\d{1,3}|[01]))s?/i', $_SERVER['HTTP_ACCEPT'], $matches)){
				$htmlQ = isset($matches[2]) ? $matches[2] : 1;
				$xhtml = ($xhtmlQ >= $htmlQ);
			}else{
				$xhtml=true;
			}
		}
		return $xhtml;
	}
	
	/**
	 * magic classes:
	 * - remove_id
	 * - remove_class
	 * - remove_parent
	 */
	private function magic_classes(){
		foreach($this->xml->getElementsByClassName('remove_id') as $node){
			$node->removeAttribute('id');
			
			$classes = preg_split('#\s#', str_replace('remove_id', '', $node->getAttribute('class')), -1, PREG_SPLIT_NO_EMPTY);
			
			if(empty($classes[0])){
				$node->removeAttribute('class');
			}else{
				$node->setAttribute('class', implode(' ', $classes));
			}
		}
		foreach($this->xml->getElementsByClassName('remove_parent') as $node){
			$this->removeParent($node);
		}
		foreach($this->xml->getElementsByClassName('remove_class') as $node){
			$node->removeAttribute('class');
		}
	}
	
	/**
	 * display
	 * @param bool debug (if true displays plain source code)
	 * magic classes
	 * display xhtml or html
	 */	
	public function display($mime=0){
		/************* xpath tests *****************
		$query = '//*:d';
		$objects = $this->xpath->query($query);
		
		//echo $objects->item(0)->lookupNamespaceURI(null);
		
		echo '<div style="border:2px solid">Obiekty:<ul>';
		foreach($objects as $object){
			echo '<li><code>'.$object->nodeName.'</code>:<code>'.$object->nodeValue.'</code></li>';
		}
		echo '</ul></div>';
		
		/************* xpath tests *****************/
		
		// excecute display functions
		/*
			display function for fragment are in xt.xml.php (look for ".php")
		*/
		
		if(file_exists($this->name.'.php')){
			require_once($this->name.'.php');
		}
		
		$this->execute_modifiers();
		$this->magic_classes();
		
		$mime_tab=array(
			2 => 'application/xhtml+xml',
			'text/html',
			'text/xml',
			'application/xml',
			'application/rss+xml',
			'application/atom+xml',
			'text/plain'
		);
		
		
		$this->output=$this->dom->savexml();
		
		// remove namespace...
		foreach($this->removedNamespaces as $ns){
			$this->output=preg_replace('#\s+xmlns:'.$ns.'=".*?"#', '', $this->output);
		}

		if($mime == 8){
			echo '<pre><code>'.htmlspecialchars($this->output).'</code></pre>';
		}elseif($mime == 0){
			return $this->output;
		}elseif($mime == 1){
			if($this->useXML){
				if(!headers_sent()){
					header('Content-Type: application/xhtml+xml; charset='.$this->encoding);
				}
				echo $this->output;
			}else{
				if(!headers_sent()){
					header('Content-Type: text/html; charset='.$this->encoding);
				}
				echo preg_replace('#<\?xml[^?]+\?>#s', '', str_replace(array('<![CDATA[', ']]>'), array('', ''), $this->output));
			}
		}elseif($mime == 3){
			echo preg_replace('#<\?xml[^?]+\?>#s', '', str_replace(array('<![CDATA[', ']]>'), array('', ''), $this->output));
		}else{
			if(!headers_sent()){
				header('Content-Type: '.$mime_tab[$mime].'; charset='.$this->encoding);
			}
			echo $this->output;
		}
	}
	
	public function __toString(){
		return $this->display(0);
	}
	
	/**
	 * modifier function like smarty's:
	 * @param mixed node
	 * @param string attribute
	 * @param callback function
	 */
	private $modifiers = array();
	public function modifier($node, $attribute, $function){
		$this->modifiers[] = array($node, $attribute, $function);
	}
	
	public function execute_modifiers(){
		foreach($this->modifiers as $array){
			foreach($this->xml->getnode($array[0]) as $node){
				$node->setAttribute(
					$array[1],
					call_user_func(
						$array[2],
						$node->getAttribute($array[1])
					)
				);
			}
		}
	}
	
	/**
	 * remove namespace
	 * @param string namespace
	 * @param bool remove_content?
	 */
	private $removedNamespaces=array();
	public function removeNS($ns, $remove_content=false){
		if(!$remove_content){
			$this->removeParent($ns.'|*');
		}else{
			$this->remove($ns.'|*');
		}
		
		$this->removedNamespaces[]=$ns;
		
		$this->root->removeAttribute('xmlns:'.$ns);
	}
	
	/**
	 * create document-fragment
	 * @param str template_fragment
	 */
	public function fragment($str=false, $is_string=false){
		$fragment=$this->fragment;
		if($str){
			$fragment->load($str, $is_string);
		}
		return $fragment;
	}
	
	public function switcher(){
		$objects=func_get_args();
		$switcher=$this->switcher;
		$switcher->load($objects);
		return $switcher;
	}
	
	public function node($css){
		return new node($this, $css);
	}
}
?>