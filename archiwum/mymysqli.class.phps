<?php
/*
 *	class mymysqli extending mysqli
 *	function safequery provides easy interface compatible with printf function
 *	i.e. safequery($query, $arg1, $arg2, ..., $argn)
 *
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
class mymysqli extends mysqli{
	public function safequery($query){
		$args=func_get_args();
		array_shift($args);
		$args=array_map(array($this, 'escape'), $args);
		$query=vsprintf($query, $args);
		$results=$this->query($query);
		if($this->error){
			echo '<div style="border:2px solid red"><p>Błąd w zapytaniu sql:<blockquote>'.$this->error.'</blockquote></p>
			<p>Zapytanie podane jako argument funkcji:<code>'.htmlspecialchars(func_get_arg(0)).'</code></p>
			<p>Argumenty:<pre>'.htmlspecialchars(print_r($args,1)).'</pre></p>
			<p>Treść zapytania to:<code>'.htmlspecialchars($query).'</code></p></div>';
		}else{
			return $results;
		}
		
	}
	
	public function escape($str){
		if(ini_get('magic_quotes_gpc')){
			$str=stripslashes($str);
		}
		return $this->real_escape_string($str);
	}
}
?>