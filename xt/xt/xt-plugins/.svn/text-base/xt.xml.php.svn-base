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
class xml{
	public function __construct(&$xt){
		$this->core=$xt;
	}

	/**
	 * tworzenie elementow dom
	 * @param string name
	 * @param string str
	 * @param array attributes
	 */
	public function create($name, $str=0, $arguments=0){
		$node = $this->core->dom->createElement($name);
		if($str){
			$this->appendText($node, $str);
		}
		if($arguments){
			$this->set($node, $arguments);
		}
		return $node;
	}
	
	/**
	 * delete parent of the node
	 * @param domnode node
	 */
	public function removeParent($name){
		if($this->is_node($name)){
			return $this->removeOneParent($name);
		}else{
			$done=true;
			foreach($this->getnode($name) as $node){
				if(!$this->removeOneParent($node)){
					$done=false;
				}
			}
			return $done;
		}
	}
	public function removeOneParent($name){
		if($node=$this->getOneNode($name)){
			foreach($node->childNodes as $child){
				$node->parentNode->insertBefore($child->cloneNode(true), $node);
			}
			$this->remove($node);
			
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * replace parent node with new_name
	 * @param mixed old
	 * @param string new
	 * @param array attributes
	 */
	public function replaceParent($name, $new_name, $attributes=0){
		if($this->is_node($name)){
			return $this->replaceOneParent($name, $new_name, $attributes=0);
		}else{
			$done=true;
			foreach($this->getnode($name) as $node){
				if(!$this->replaceOneParent($node, $new_name, $attributes=0)){
					$done=false;
				}
			}
			return $done;
		}
	}
	
	public function replaceOneParent($name, $new_name, $attributes=0){
		if($node=$this->getOneNode($name)){
			$old=$this->core->dom->createdocumentfragment();
			foreach($node->childNodes as $child){
				$old->appendChild($child->cloneNode(true));
			}
			$new=$this->create($new_name, 0, $attributes);
			$new->appendChild($old);
			$this->insertBefore($node,$new);
			$this->remove($node);
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * check if object $node is domelement or domdocumentfragment or dom-sth
	 * @param mixed
	 * @return bool is_dom_object
	 */
	public function is_node($node){
		return substr(print_r($node,true),0,3)==='DOM';
	}
	
	/**
	 * dodanie zawartości tekstowej obiektowi
	 * @param object domelement
	 * @param str append-text
	 */
	public function appendText($node, $str){
		if($this->is_node($node)){
			if($child=$this->text2html($str)){
				if($child->hasChildNodes()){
				// prevent ,,Warning: DOMNode::appendChild(): Document Fragment is empty''
					$node->appendChild($child);
				}
			}
		}
	}
	
	/**
	 * dodanie zawartości tekstowej obiektowi, ale na początku
	 * @param object domelement
	 * @param str append-text
	 */
	public function appendStartText($node, $str){
		if($this->is_node($node)){
			if($node->hasChildNodes()){
				if($child=$this->text2html($str)){
					if($child->hasChildNodes()){// prevent ,,Warning: DOMNode::appendChild(): Document Fragment is empty''
						$this->insertBefore($node->firstChild, $child);
					}
				}
			}else{
				$this->appendText($node, $str);
			}
		}
	}
	
	/**
	 * zamiana tekstu na obiekt dom
	 * @param str text
	 * @return object domelement
	 */
	public function text2html($str){ 
		if($this->is_node($str)){
			return $str;
		}elseif(is_string($str)){
			$fragment=$this->core->dom->createDocumentFragment();
			
			$str = $this->core->entities($str);
			
			$fragment->appendXML('<root '.$this->core->namespaces.'>'.$str.'</root>');
			
			$root = $fragment->firstChild;
			
			// pętla od końca, bo drzwo modyfikowane jest na żywo!
			for($i=$root->childNodes->length-1; $i>=0; $i--){
				$child = $root->childNodes->item($i);
				
				$fragment->insertBefore($child, $fragment->firstChild);
			}
			
			$fragment->removeChild($root);
			
			return $fragment;
		}elseif($str instanceof fragment){
			return $str->s;
		}else{
			return null;
		}
	}
	
	/**
	 * sprawdza, czy node jest dzieckiem głównego dokumentu
	 * @param object domelement
	 * @return bool
	 */
	public function checkNode($node){
		if(!$this->is_node($node)){
			return false;
		}else{
			return $node->ownerDocument==$this->core->dom;
		}
	}
	
	/**
	 * pobieranie elementów
	 * @param mixed reference_to_object_css_like
	 * @return null / object domnode
	 */
	public function getnode($name, $parent=0, $count=false){
		if($this->is_node($name)){
			return $name;
		}else{
			$query = new css2xpath($name, $this->core->xpath->defaultNamespace);
			if(!$parent){
				$parent=$this->core->root;
			}
			
			return $this->core->xpath->query('.'.$query->xpath, $parent);
		}
	}
	
	public function getOneNode($name, $parent=0){
		if($this->is_node($name)){
			return $name;
		}else{
			$nodes=$this->getnode($name, $parent, 0); // getnode($name.':first-of-type', $parent);
			
			return $nodes->item(0);
		}
	}
	
	/**
	 * usuwa id wszystkich dzieci i danego obiektu
	 * @param mixed object
	 */
	public function remove_id($name){
		if($node=$this->getOneNode($name)){
			$query = './descendant-or-self::*[@id]';
			$entries = $this->xpath->query($query, $node);
			foreach($entries as $node){
				$node->removeAttribute('id');
			}
		}else{
			return false;
		}
	}
	
	/**
	 * function add should add values to all nodes which match the css pattern
	 */
	public function add($name, $value){
		if($this->is_node($name)){
			return $this->addOne($name, $value);
		}else{
			$done=true;
			foreach($this->getnode($name) as $node){
				if(!$this->addOne($node, $value)){
					$done=false;
				}
			}
			return $done;
		}
	}
	
	/**
	 * głowna funkcja dodająca wartości/parametry, obsługująca pętle
	 */
	public function addOne($name, $value){
		if($node=$this->getOneNode($name)){
			if(is_array($value) && isset($value[0]) && is_array($value[0])){
				return $this->r($node, $value);
			}elseif(is_scalar($value)){
				return $this->appendText($node, (string)$value);
			}elseif($this->is_node($value)){
				return $node->appendChild($value);
			}elseif($value instanceof fragment){
				
				
				$value->execute_display();
				
				return $node->appendChild($value->s);
			}elseif($value[0] instanceof xt_loop){
				$this->loop_xt($node, $value);
			}elseif(is_array($value)){
				return $this->set($node, $value);
			}else{
				throw new xtException('Niepoprawny drugi parametr metody <code>add</code>: <code>'.htmlspecialchars(print_r($value, 1)).'</code>', E_WARNING);
			}
		}else{
			return false;
		}
	}
	
	/**
	 * loop_xt
	 */
	public function loop_xt($node, $value){
		foreach($value as $xt_loop){
			$clone = $node->cloneNode(true);
			
			
			
			// wykonaj wszystkie zebrane te
			foreach($xt_loop->f as $array){
				#FIXME
				#	zadziałają tylko funkcje, gdzie pierwszwym parametrem jest obiekt node (względnie tekstowe odwołanie)
				
				$array[1][0] = $this->getOneNode($array[1][0], $clone);
			
				call_user_func_array(array('xml', $array[0]), $array[1]);
				
				
			}
			
			$node->parentNode->insertBefore($clone, $node);
		}
		
		$this->remove($node);
	}

	/**
	 * pomocnicza funkcja głównej - zagnieżdżone pętle
	 */
	private function r($node, $all){
		$done=true;
		foreach($all as $row){
			if(!$clone=$node->cloneNode(true)){
				$done=false;
			}
			foreach($row as $key => $value){
				if($tt=$this->getOneNode($key, $clone)){
					if(!$this->add($tt, $value)){
						$done=false;
					}
				}
			}
			$this->remove_id($clone);
			if(!$node->parentNode->insertBefore($clone, $node)){
				$done=false;
			}
		}
		if(!$node->parentNode->removeChild($node)){
			$done=false;
		}
		return $done;
	}
	
	/**
	 * pętla drugiego rodzaju
	 * nie działa jak należy (?)
	 */
	public function loop($name, $count, $delete_sample=true){
		if($node=$this->getOneNode($name)){
			$str=$node->ownerDocument->savexml($node);
			$count=(int)$count;
			if($count>0){
				for($i=0; $i<$count; $i++){
					$fragment=$this->fragment($str, true);
					
					$node->parentNode->insertBefore($fragment->s, $node);
					
					$fragment->root=$node->previousSibling; //konieczne jest ustawienie rodzica, gdyż w tym miejscu tracimy #document-fragment

					$return[]=$fragment;
				}
				if($delete_sample){
					$this->remove($node); // usuń wzorcowy element
				}
				return $return;
			}else{
				return array();
			}
		}else{
			return array();
		}
	}
	
	/**
	 * nadawanie atrybutów obiektowi
	 * arguemtny w tablicy lub jako kolejene parametry funkcji
	 */
	public function set($name, $attributes){
		if($this->is_node($name)){
			return $this->setOne($name, $attributes);
		}else{
			$done=true;
			foreach($this->getnode($name) as $node){
				if(!$this->setOne($node, $attributes)){
					$done=false;
				}
			}
			return $done;
		}
	}
	
	public function setOne($node, $attributes){
		if(!is_array($attributes)){
			throw new xtException('Metoda <code>set</code> potrzebuje tablicy jako argumentu.', E_WARNING);
		}
		if($node=$this->getOneNode($node)){
			foreach($attributes as $attribute => $value){
				if(is_string($attribute) && is_scalar($value)){
					$value=(string)$value;
					$this->setAttribute($node, $attribute, $value);
				}
			}
		}else{
			return false;
		}
	}
	
	/**
	 * function setting attribute with magic attribute #text
	 */
	public function setAttribute($node, $attribute, $value){
		if($this->is_node($node)){
			if($attribute!=='#text'){
				$node->setAttribute($attribute, $value);
			}else{
				$child = $this->text2html($value);
				while($node->hasChildNodes()){
					$node->removeChild($node->firstChild);
				}
				$node->appendChild($child);
			}
		}
	}
	
	/**
	 * usuwanie obiektów
	 * zwraca usuwane dziecko
	 */
	public function remove($name){
		if($this->is_node($name)){
			return $this->removeOne($name);
		}else{
			$done=true;
			foreach($this->getnode($name) as $node){
				if(!$this->removeOne($node)){
					$done=false;
				}
			}
			return $done;
		}
	}
	public function removeOne($name){
		if($node=$this->getOneNode($name)){
			return $node->parentNode->removeChild($node);
		}else{
			return false;
		}
	}
	
	/**
	 * tak jak domowa, nie obsługuje pętli
	 * UWAGA!!! niekompatybilny z domowym!!!
	 */
	public function insertBefore($old, $new){
		if($old=$this->getOneNode($old)){
			if($this->is_node($new) && $this->is_node($old)){
				$old->parentNode->insertBefore($new, $old);
			}elseif($new=$this->text2html($new)){
				$old->parentNode->insertBefore($new, $old);
			}
		}else{
			return false;
		}
	}
	
	/**
	 * jw, tylko dodwawanie po,
	 * dodać sprawdzanie
	 * && $this->is_node($old->nextSibling)
	 */
	public function insertAfter($old, $new){
		if($old=$this->getOneNode($old)){
			//if(!$this->is_node($new)){
			//	$new=$this->text2html($new);
			//}
			
			$fragment=$this->core->dom->createDocumentFragment();
			$this->add($fragment, $new); # some problems with loop
			
			if($this->is_node(nextSibling)){
				$this->insertBefore($fragment, $old->nextSibling);
			}else{
				$old->parentNode->appendChild($fragment);
			}
		}else{
			return false;
		}
	}
	
	/**
	 * clone - return new fragment
	 */
	public function clone_node($node, $remove_parent=0){
		if($node=$this->getOneNode($node)){
			$fragment=$this->fragment($node->ownerDocument->savexml($node));
			return $fragment;
		}else{
			return null;
		}
	}
	
	/**
	 * something like
	 * {if condition}
	 *   object
	 * {/if}
	 *
	 * if condition isn't true - delete object
	 */
	public function condition(){
		if(func_num_args()>0 && func_num_args()%2==1){
			for($i=0, $count=func_num_args(); $i<$count; $i+=2){
				if(func_get_arg($i-1)){
					
				}
			}
		}
		if($node=$this->getOneNode($object)){
			if(!$condition){
				$this->remove($node);
			}
		}
	}
	
	/**
	 * zwraca listę obiektów w formie domnodelist, using xpath - faster!
	 * @param mixed klasa
	 * @param domnode parent
	 * @param string tag_nane
	 */
	public function getElementsByClassName($class, $parent=0, $name=0){
		if(!$parent){
			$parent=$this->core->root;
		}
		if(!$name){
			$name='*';
		}
		if(!is_array($class)){
			$class=array($class);
		}
		$query = '//'.$name.'[';
		foreach($class as $c){
			$query.='contains(concat(" ", @class, " "), " '.$c.' ") and ';
		}
		$query=substr($query, 0, -4);
		$query.=']';
		
		return $this->core->xpath->query($query, $parent);
	}
	
	/**
	 * getElementsByTagName, teoretycznie niepotrzebna
	 */
	public function getElementsByTagName($tag, $parent=0){
		if(!$parent){
			$parent=$this->core->root;
		}
		return $parent->getElementsByTagName($tag);
	}
	
	/**
	 * zwraca tag element o podanej nazwie
	 */
	public function getElementByTagName($tag, $parent=0, $count=0){
		if(!$parent){
			$parent=$this->core->root;
		}
		return $this->getElementsByTagName($tag, $parent)->item($count);
	}
	
	/**
	 * zwraca obiekt mając za parametr jego id
	 * @param str object
	 * @param object domnode 
	 * @param str node name
	 * @return object domnode
	 */
	public function getElementById($id, $parent=0, $node_name=0){
		if(!$parent){
			$parent=$this->core->root;
		}
		if(!$node_name){
			$node_name='*';
		}
		$query = './descendant-or-self::'.$node_name.'[@id="'.$id.'"]';
		$entries = $this->xpath->query($query, $parent);
		return $entries->item(0);
	}
}
?>