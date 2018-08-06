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
/**
* it's not real tidy
* only clean dirty html - unclosed tags, and so on
* needs some improvements!
*/
class small_tidy{

	private function is_open($str){
		return preg_match('#<(?!(?:/|\?|!))[^>]+(?<!/)>#', $str);
	}
	
	private function is_close($str){
		return preg_match('#</[^>]+(?<!/)>#', $str);
	}
	private function get_node($str){
		preg_match('#[^</> ]+#', $str, $test);
		return $test[0];
	}
	
	public function clean($str){
		preg_match_all('#(?:<[^>]+>)|(?:[^<]+)#', $str, $all);
		$open=array();
	
		$end='';
			
		$nie_zagniezdzane=array('li', 'html', 'head', 'body', 'p');
		
		$jednotagowe=array('meta');
		
		foreach($all[0] as $node){
			if($this->is_open($node)){
				if(in_array($this->get_node($node), $jednotagowe)){
					$end.='<'.substr($node, 1, -1).' />';
				}else{
					if(in_array($this->get_node($node), $nie_zagniezdzane) && $open[count($open)-1]==$this->get_node($node)){
						$end.= '</'.$this->get_node($node).'>';
						array_pop($open);
					}
					
					
					$open[]=str_replace(array('<','>'), array('',''),$node);
					
					$end.=$node;
				}
				
			}elseif($this->is_close($node)){
			
				if($this->get_node($node)==$open[count($open)-1]){
					$end.= $node;
					array_pop($open);
				}elseif(count($open)>0){
					$end.= '</'.$this->get_node(array_pop($open)).'>';
				}
			}else{
				$end.= $node;
			}
		}
		
		for($i=count($open)-1; $i>=0; $i--){
			$end.='</'.$this->get_node($open[$i]).'>';
		}
		
		return $end;
	}
}
?>