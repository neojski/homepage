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
class switcher{
	public function __construct(&$xt){
		$this->core=$xt;
	}
	
	public function load($objects){
		if(is_array($objects)){
			$this->objects=$objects;
		}else{
			throw new xtException('Funkcja switcher wymaga podania argumentów!!!!!!!!1');
		}
	}
	
	public function choose($object){
		if(is_int($object)){
			if(isset($this->objects[$object])){
				unset($this->objects[$object]);
			}
		}else{
			if(($key=array_search($object, $this->objects))!==false){
				unset($this->objects[$key]);
			}
		}
		foreach($this->objects as $object){
			if($node=$this->core->getOneNode($object)){
				$this->core->remove($node);
			}
		}
	}
}
?>