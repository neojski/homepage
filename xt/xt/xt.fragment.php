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
	public function __construct($file=0, $xml){
		$this->parent=$xml;
		if($file){
			$this->load($file);
		}
		parent::__construct();
	}
	public function load($file){
		if(is_file($file)){
			if(file_exists($file)){
				$this->template=file_get_contents($file);
			}else{
				$this->error('Template file '.$file.' not found.');
			}
		}elseif(is_string($file)){
			$this->template=$file;
		}else{
			$this->error('Incompatible template type');
		}
		$this->template=$this->tidy($this->template);
		
		$this->s=$this->parent->createDocumentFragment(); //tej durnej nazwy używa add() ;-)
		$this->s->appendXML($this->template);
		
		$xpath = new DOMXPath($this->parent);
		$this->root=$xpath->query('.', $this->s)->item(0);
		
		$this->xml=$this->parent;
	}
	
	public function getElementsByTagName($tag, $parent=0){
		if(!$parent){
			$parent=$this->root;
		}
		$xpath = new DOMXPath($this->xml);
		$query = './descendant::'.$tag;
		return $xpath->query($query, $parent);
	}
}
?>