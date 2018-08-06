<?php
/*
 *	xt templates system
 *	Copyright :(C) 2007 Tomasz 'neo' Kołodziejski <tkolodziejski at gmail dot com>, <neo007 at jabber dot com>
 *	E-mail    :tkolodziejski@gmail.com
 *	Website   :http://neo.mlodzi.pl/xt
 *	
 *	node object creator
 *	This is a part of xt library.
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

class node{
	public function __construct(&$core, $css){
		$this->core=$core;
		$this->css=$css;
		
		$this->node=$this->core->xml->getonenode($css);
	}
	
	public function add($str){
		$this->core->xml->add($this->node, $str);
		return $this;
	}
	
	public function set($attributes){
		$this->core->xml->set($this->node, $attributes);
		return $this;
	}
	
	public function remove(){
		$this->core->xml->remove($this->node);
		return $this;
	}
	
	public function css($css){
		$this->set(array('style' => $css));
		return $this;
	}
	
	public function style($css){
		$this->css($css);
	}
	
	/*
		read as insertBeforeMe
	*/
	public function insertBefore($new_node){
		$this->core->xml->insertBefore($this->node, $new_node);
		return $this;
	}

	public function insertAfter($new_node){
		$this->core->xml->insertAfter($this->node, $new_node);
		return $this;
	}
	
	public function __get($name){
		if($name=='value'){
			$this->__toString();
		}
	}
	
	public function __set($name, $value){
		$this->$name=$value;
		
		if($name=='value'){
			$this->add($value);
			unset($this->value); // unset, to call every time __set function
		}
		
		return $this;
	}
	
	public function __toString(){
		return $this->node->ownerDocument->savexml($this->node);
	}
}
?>