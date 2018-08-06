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
 
class js{
	public function __construct(&$xt){
		$this->core=$xt;
	}
	
	public function add($str, $new=0){
		if($new){
			$this->core->head->appendChild($this->core->create('script', '<![CDATA['. trim($str) .']]>', array('type'=>'text/javascript')));
		}else{
			if($style=$this->core->getElementByTagName('script', $this->core->head)){
				$style->firstChild->data.=trim($str);
			}else{
				$this->add($str, 1);
			}
		}
	}
	
	public function file($url){
		$link=$this->core->xml->create('script', null, array('type'=>'text/javascript', 'src'=>$url));
		$this->core->root->getElementsByTagName('head')->item(0)->appendChild($link);
	}
}
?>