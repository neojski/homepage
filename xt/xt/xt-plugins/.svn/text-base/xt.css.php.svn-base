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
 
class css{
	public function __construct(&$xt){
		$this->core=$xt;
	}
	
	public function add($str, $new=0){
		if($new){
			$this->core->head->appendChild($this->core->create('style', '<![CDATA['. trim($str) .']]>', array('type'=>'text/css')));
		}else{
			if($style=$this->core->getElementByTagName('style', $this->core->head)){
				$style->firstChild->data.=trim($str);
			}else{
				$this->add($str, 1);
			}
		}
	}
	
	public function file($url, $title=false, $media=false){
		$link=$this->core->create('link', null, array('rel'=>'stylesheet', 'href'=>$url, 'title'=>$title, 'type'=>'text/css', 'media'=>$media));
		$this->core->head->appendChild($link);
	}
	
	public function set($name, $style){
		if($node=$this->core->getOneNode($name)){
			$node->setAttribute('style', $style);
		}
	}
}
?>