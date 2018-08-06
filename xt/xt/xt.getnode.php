<?php
/*
 *	xt templates system
 *	Copyright :(C) 2007 Tomasz Kołodziejski
 *	E-mail    :tkolodziejski@gmail.com
 *	
 *	CSS3 to XPATH1.0 translator
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
class getNode{
	public function __construct($xt){
		$this->debug=0;

		$this->xml=$xt->xml;
		$this->root=$xt->root;
		
		$this->method=$xt->getnode_method;
	}
	
	public function get($str, $parent=0){
		$this->parent=$parent?$parent:$this->root;
		if($this->method===2){
			$this->xpath='.';
		
			$match=preg_split('#(\s*(?:>|(?<!n)\+|~(?!=))\s*|\s+)#', trim($str), -1, PREG_SPLIT_DELIM_CAPTURE);
			array_unshift($match, null);
			
			if($this->debug){
				echo '<pre>tablica ';
				print_r($match);
				echo '</pre>';
			}
			
			$count=count($match);
			for($i=0; $i<$count; $i+=2){
				$str=$match[$i+1];
				$glue=$match[$i];
				
				$this->add($str, $glue);
			}
			
			$objects=$this->getobjects();
			
			if($this->debug){
			echo 'Lista<ul>';
			foreach($objects as $object){
				echo '<li>'.$object->nodeName.'</li>';
			}
			echo '</ul>';}
			
			return $objects;
		}elseif($this->method===1){
			$this->xpath=$str;
			$objects=$this->getobjects();
			return $objects;
		}
	}
	
	private function add($str, $glue=0){
		/*$unicode='[0-9a-f]{1,6}(?:\r\n|[ \n\r\t\f])?';
		$nonascii='[^\0-\177]';
		$escape=$unicode.'|[^\n\r\f0-9a-f]';
		
		$nmchar='[_a-z0-9-]|'.$nonascii.'|'.$escape;
		$nmstart='[_a-z]|'.$nonascii.'|'.$escape;
		$ident='-?'.$nmstart.$nmchar.'*';
		$string=$string1.'|'.$string2;
		$string1='"(?:[^\n\r\f\\"]|\\'.$nl.'|'.$nonascii.'|'.$escape.')*"';
		$string1='\'(?:[^\n\r\f\\\']|\\'.$nl.'|'.$nonascii.'|'.$escape.')*\'';
		$function=$ident.'(?:';
		$universal='\*';
		$num='[0-9]+|[0-9]*\.[0-9]+';
		$expression='(?:(?:\+|-|'.$num.$ident.'|'.$num.'|'.$string.'|'.$ident.')\s*)+';
		$funcitonal_pseudo=$function.'\s*'.$expression.')';
		
		$type_selector=$ident;
		
		$name=$nmchar.'+';
		$hash='\#'.$name;
		$class='\.'.$ident;
		$attrib='\[\s*'.$ident.'\s*(?:(?:\^=|\$=|\*=|=|~=|\|=)\s*(?:'.$ident.'|'.$string.')\s*)?\]';
		$pseudo=':(?:'.$ident.'|'.$funcitonal_pseudo.')';
		
		$negation_arg=$type_selector.'|'.$universal.'|'.$hash.'|'.$class.'|'.$attrib.'|'.$pseudo;
		$negation=':not\(\s*'.$negation_arg.'\s*\)';
		
		echo '#('.$type_selector.'|'.$universal.')('.$hash.'|'.$class.'|'.$attrib.'|'.$pseudo.'|'.$negation.')*|('.$hash.'|'.$class.'|'.$attrib.'|'.$pseudo.'|'.$negation.')+#';
		
		preg_match_all('#(('.$type_selector.'|'.$universal.')('.$hash.'|'.$class.'|'.$attrib.'|'.$pseudo.'|'.$negation.')*|('.$hash.'|'.$class.'|'.$attrib.'|'.$pseudo.'|'.$negation.')+)#', $str, $match);
		
		print_r($match);
		
		
		
		preg_match_all('#(('.$type_selector.'|'.$universal.')('.$hash.'|'.$class.'|'.$attrib.'|'.$pseudo.'|'.$negation.')*|('.$hash.'|'.$class.'|'.$attrib.'|'.$pseudo.'|'.$negation.')+)#', $str, $match);
		
		print_r($match);*/
		
		$name='[a-z0-9]+';
		
		$glue=$this->getglue($glue);
		
		if(preg_match('#^'.$name.'#', $str, $match)){
			// tak, mamy nazwę
			$name=$match[0];
			
			$this->xpath.=$glue.$name;
			
			$str=substr($str, strlen($name));
			
		}else{
			// nie, nie podano nazwy
			
			$this->xpath.=$glue.'*';
		}
		
		preg_match_all('#\#[a-z0-9]+|\.[a-z0-9]+|\[[a-z0-9]+(?:[*~|^$]?="[a-z0-0]+")?\]|:[a-z-]+(?:\(.*?\))?#', $str, $match);
		
		if($this->debug){
			print_r($match);
		}
		
		if(empty($match[0])){
			// nothing ?
		}else{
			foreach($match[0] as $param){
				$this->addParam($param);
			}
		}
	}
	
	private function addParam($param){
		if($this->debug){
			echo '<code>'.$param.'</code>';
		}
		
		if(substr($param, 0, 4)==':not'){
			$not=true;
			$param=substr($param, 5, -1);
		}else{
			$not=false;
		}
		
		switch($param{0}){
			case '#':
				//po id
				$this->g_id($param, $not);
			break;
			
			case '.':
				//klasa
				$this->g_class($param, $not);
			break;
			
			case '[':
				//atrybut
				$this->g_attribute($param, $not);
			break;
			
			case ':':
				//pseudo-klasa
				$this->g_pseudoclass($param, $not);
			break;
			
			default:
				//nic z tych rzeczy, zatem po nazwie + not
				if($not){
					$this->xpath.='[name()!="'.$param.'"]';
				}else{
					//nigdy nie powinno się zdarzyć
				}
			break;
		}
	}
	
	private function g_id($id, $not=false){
		$id=substr($id, 1);
		
		if(!$not){
			$this->xpath.='[@id="'.$id.'"]';
		}else{
			$this->xpath.='[@id!="'.$id.'"]';
		}
	}
	
	private function g_class($class, $not=false){
		$class=substr($class, 1);
		
		/*if(!$not){
			$this->xpath.='[contains(concat(" ", @class, " "), " '.$class.' ")]';
		}else{
			$this->xpath.='[not(contains(concat(" ", @class, " "), " '.$class.' "))]';
		}*/
		$this->g_attribute('[class~="'.$class.'"]', $not);
	}
	
	private function g_attribute($attribute, $not){
		preg_match('#\[([a-z0-9]+)(?:([~^$*|]?)="([a-z0-9]+)"\])?#', $attribute, $match);
		
		$attribute=$match[1];
		$separator=$match[2];
		$value=$match[3];
		
		if($value){
			switch($separator){
				case '':
					$match='@'.$attribute.'="'.$value.'"';
					break;
				case '~':
					$match='contains(concat(" ", @'.$attribute.', " "), " '.$value.' ")';
					break;
				case '^':
					$match='starts-with(@'.$attribute.', '.$value.')';
					break;
				case '$':
					$match='substring(@'.$attribute.', string-length(@'.$attribute.')-'.strlen($value).')="'.$value.'"';
					break;
				case '*':
					$match='contains(@'.$attribute.', "'.$value.'")';
					break;
				case '|':
					$match='@'.$attribute.'="'.$value.'" or contains(@'.$attribute.', " '.$value.'-") or starts-with(@'.$attribute.', "'.$value.'-")';
					break;
			}
		}else{
			$match='@'.$attribute.'';
		}
		
		if(!$not){
			$this->xpath.='['.$match.']';
		}else{
			$this->xpath.='[not('.$match.')]';
		}
	}
	
	private function g_pseudoclass($class, $not){
		preg_match('#^(\:[a-z-]+)(?:\((.*?)\))?$#', $class, $match);
		
		//print_r($match);
		
		$param=$match[2];
		$match=$match[1];
		
		switch($match){
			case ':first-child':
				$match='../*[1]=.';
			break;
			
			case ':last-child':
				$match='../*[position()=last()]=.';
			break;
			
			case ':first-of-type':
				$match='position()=1';
			break;
			
			case ':last-of-type':
				$match='position()=last()';
			break;
			
			case ':only-of-type':
				$match='position()=1 and position()=last()';
			break;
			
			case ':only-child':
				$match='../*[position()=1 and position()=last()]=.';
			break;
			
			case ':root':
				$match='/=.';
			break;
			
			case ':empty':
				$match='count(./child::node())=0';
			break;
			
			case ':lang':
				$this->g_attribute('[lang|="'.$param.'"]', $not);
			break;
			
			case ':nth-child':
				/* nth-child start
				 */
				
				if(preg_match('#(-?(\d+)?)n#', $param, $a)){
					if(empty($a[1])){
						$a=1;
					}elseif(empty($a[2])){
						$a=-1;
					}else{
						$a=(int)$a[1];
					}
				}else{
					$a=null;
				}
				
				preg_match('#(-?\d+)(?!n)#', $param, $b);
				if(empty($b[1])){
					$b=0;
				}else{
					$b=(int)$b[1];
				}
				
				if($a==1){
					$match='../*[position()>'.($b-1).']=.';
				}elseif($a==-1){
					$match='../*[position()<'.($b-1).']=.';
				}elseif($a){
					$match='.=../*[(position()+'.(-$b).')*'.$a.'>=0 and (position()+'.(-$b).') mod '.$a.'=0]';
				}else{
					$match='../*[position()='.$b.']=.';
				}
				/* nth-child end */
			break;
			
			case ':nth-of-type':
				/* nth-of-type start */
				
				if(preg_match('#(-?(\d+)?)n#', $param, $a)){
					if(empty($a[1])){
						$a=1;
					}elseif(empty($a[2])){
						$a=-1;
					}else{
						$a=(int)$a[1];
					}
				}else{
					$a=null;
				}
				
				preg_match('#(-?\d+)(?!n)#', $param, $b);
				if(empty($b[1])){
					$b=0;
				}else{
					$b=(int)$b[1];
				}
				
				if($a==1){
					$match='position()>'.($b-1);
				}elseif($a==-1){
					$match='position()<'.($b-1);
				}elseif($a){
					$match='(position()+'.(-$b).')*'.$a.'>=0 and (position()+'.(-$b).') mod '.$a.'=0';
				}else{
					$match='position()='.$b;
				}
				/* nth-of-type end */
			break;
		}
		
		if(!$not){
			$this->xpath.='['.$match.']';
		}else{
			$this->xpath.='[not('.$match.')]';
		}
	}
	
	private function getobjects(){
		if(empty($this->xpath)){
			return null;
		}else{
			$xpath = new DOMXPath($this->xml);
			$results = $xpath->query($this->xpath, $this->parent);
			
			if($this->debug){
				echo '<p>Zapytanie to: <code>'.$this->xpath.'</code></p>';
			}
			
			return $results;
		}
	}
	
	private function getglue($glue){
		if(is_null($glue)){
			return '/descendant-or-self::';
		}else{
			$glue=trim($glue);
			switch($glue){
				case '':
					$glue='/descendant::';
					break;
				case '>':
					$glue='/';
					break;
				case '+':
					$glue='/following::*[1]/self::';
					break;
				case '~':
					$glue='/following::';
					break;
			}
			return $glue;
		}
	}
}

?>