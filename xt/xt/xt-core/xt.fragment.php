<?php
/*
 *	xt templates system
 *	Copyright :(C) 2007 Tomasz Kołodziejski
 *	E-mail    :tkolodziejski@gmail.com
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
class fragment extends xt{
	public function __construct(&$xt){
		$this->parent=$xt->dom;
		parent::__construct();//should be removed?
	}
	
	/*
		powinien być kompatybilny z load z xt.core.php	
	*/
	public function load($file, $is_string=false){
		if(!$is_string){
			if(file_exists($file)){
				$this->template=file_get_contents($file);
			}else{
				throw new xtException('Plik szablonu <code>'.htmlspecialchars($file).'</code> nie istnieje', E_ERROR);
			}
		}else{
			$this->template=$file;
		}
		
		$this->entities();
		
		$this->s=$this->parent->createDocumentFragment(); //tej durnej nazwy używa add() ;-)
		$this->s->appendXML($this->template);
		
		$xpath = new DOMXPath($this->parent);
		$this->root=$xpath->query('.', $this->s)->item(0);
		
		$this->dom=$this->parent;
		
		$this->name=$file;
		$this->xpath=new DOMXPath($this->dom);
	}
	public function getElementsByTagName($tag, $parent=0){
		if(!$parent){
			$parent=$this->root;
		}
		$xpath = new DOMXPath($this->dom);
		$query = './descendant::'.$tag;
		return $xpath->query($query, $parent);
	}
	
	public function execute_display(){
		if(file_exists($this->name.'.php')){
			require_once($this->name.'.php');
		}
	}
	
	/**
	 * display fragment
	 */
	public function display($mime_compability_with_core_dont_use=0){
		return $this->dom->savexml($this->s);
	}
	
	/**
	 * if conversion to string - display
	 */
	public function __toString(){
		return $this->display(0);
	}
}
?>