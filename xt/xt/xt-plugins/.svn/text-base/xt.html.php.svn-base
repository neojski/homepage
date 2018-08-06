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
class html{
	public function __construct(&$xt){
		$this->core=$xt;
	}
	
	public function __get($name){
		if($name=='form'){
			return new form($this);
		}
	}
	
	public function title($str, $replace=false){
		if($title=$this->core->xml->getElementByTagName('title')){
			if(!$replace){
				$title->nodeValue.=$str;
			}else{
				$title->nodeValue=$str;
			}
			return true;
		}else{
			return false;
		}
	}
}

class form{
	public function __construct($html){
		$this->core=$html->core;
	}
	
	public function memory($css){
		/* post */
		/* sprawdź wszystkie inputy */
		foreach($this->core->xml->getnode($css.'[method="post"] input[name][type="text"]') as $input){
			$name=$input->getAttribute('name');
			if(isset($_POST[$name])){
				$input->setAttribute('value',$_POST[$name]);
			}
		}
		
		/* sprawdź pola textarea */
		foreach($this->core->xml->getnode($css.'[method="post"] textarea[name]') as $textarea){
			$name=$textarea->getAttribute('name');
			if(isset($_POST[$name])){
				$textarea->nodeValue=$_POST[$name];
			}
		}

		/* sprawdź pola select */
		foreach($this->core->xml->getnode($css.'[method="post"] select[name]') as $select){
			$name=$select->getAttribute('name');
			if(isset($_POST[$name])){
				if($option=$this->core->xml->getOneNode($css.'[method="post"] select[name="'.$name.'"] option[value="'.$_POST[$name].'"]')){
					$option->setAttribute('selected', 'selected');
				}
			}
		}
		
		/* pole multiple select, kończący się na [] */
		foreach($this->core->xml->getnode($css.'[method="post"] select[name$="[]"][multiple="multiple"]') as $select){
			$name = substr($select->getAttribute('name'), 0, -2);
				
			if(isset($_POST[$name])){
				foreach($_POST[$name] as $option_value){
					if($option = $this->core->xml->getOneNode($css.'[method="post"] select[name="'.$name.'[]"] option[value="'.$option_value.'"]')){
						
						$option->setAttribute('selected', 'selected');
					}
				}
			}
		}
		
		
		/* to samo, tylko get */
		/* sprawdź wszystkie inputy */
		foreach($this->core->xml->getnode($css.'[method="get"] input[name][type="text"]') as $input){
			$name=$input->getAttribute('name');
			if(isset($_GET[$name])){
				$input->setAttribute('value',$_GET[$name]);
			}
		}
		
		/* sprawdź pola textarea */
		foreach($this->core->xml->getnode($css.'[method="get"] textarea[name]') as $textarea){
			$name=$textarea->getAttribute('name');
			if(isset($_GET[$name])){
				$textarea->nodeValue=$_GET[$name];
			}
		}

		/* sprawdź pola select */
		foreach($this->core->xml->getnode($css.'[method="get"] select[name]') as $select){
			$name=$select->getAttribute('name');
			if(isset($_GET[$name])){
				if($option=$this->core->getOneNode($css.'[method="get"] select[name="'.$name.'"] option[value="'.$_GET[$name].'"]')){
					$option->setAttribute('selected', 'selected');
				}
			}
		}
		
		/* pole multiple select, kończący się na [] */
		foreach($this->core->xml->getnode($css.'[method="get"] select[name$="[]"][multiple="multiple"]') as $select){
			$name = substr($select->getAttribute('name'), 0, -2);
				
			if(isset($_GET[$name])){
				foreach($_GET[$name] as $option_value){
					if($option = $this->core->xml->getOneNode($css.'[method="post"] select[name="'.$name.'[]"] option[value="'.$option_value.'"]')){
						
						$option->setAttribute('selected', 'selected');
					}
				}
			}
		}
	}
}
?>