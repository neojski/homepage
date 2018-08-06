<?php

/*
 *	scaffold like in cakephp
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

class scaffold{
	public function __construct($connect, $table, $columns, $where=''){
		$this->sql = $connect;
		$this->table = $table;
		$this->columns = $columns;
		
		$this->parse_url($_SERVER['PATH_INFO']);
		
		$this->where = $where;
		
		// submit all
		$this->submit_edit();
		$this->submit_delete();
		$this->submit_add();
	}
	
	private function safe($str){
		if(ini_get('magic_quotes_gpc')){
			$str=stripslashes($str);
		}
		return $this->sql->real_escape_string($str);
	}
	
	private function submit_edit(){
		if(isset($_POST['submit_edit'])){
			$query = 'UPDATE `'.$this->table.'` SET ';
			
			$updates = array();
			foreach($this->columns as $column => $desc){
				if(!isset($desc['readonly']) or !$desc['readonly']){
					$updates[] = $column.'="'.$this->safe($_POST[$column]).'"';
				}
			}
			
			$query .= implode(', ', $updates);
			
			$query .= ' WHERE `id`='.$this->safe($_POST['index']);
			
			$this->sql->query($query);
		}
	}
	
	private function submit_delete(){
		if(isset($_POST['submit_delete'])){
			$query = 'DELETE FROM `'.$this->table.'`';
			
			$query .= ' WHERE `id`='.$this->safe($_POST['index']);
		
			$this->sql->query($query);
		}
	}
	
	private function submit_add(){
		if(isset($_POST['submit_add'])){
			$query = 'INSERT INTO `'.$this->table.'` ('.$this->safe(implode(',', array_keys($this->columns))).') VALUES (';
			
			$inserts = array();
			foreach($this->columns as $column => $desc){
				$inserts[] = '"'.$this->safe($_POST[$column]).'"';
			}
			
			$query .= implode(', ', $inserts);
			
			$query .= ')';
			
			$this->sql->query($query);
		}
	}
	
	/*
		modes:
		0 - view
		1 - edit
		2 - add
		3 - delete
	*/
	private function parse_url($url){
		if(substr($url, 0, 5)=='/edit'){
			$this->mode = 1;
			$this->index = substr($url, 6);
		}elseif(substr($url, 0, 4)=='/add'){
			$this->mode = 2;
		}elseif(substr($url, 0, 7)=='/delete'){
			$this->mode = 3;
			$this->index = substr($url, 8);
		}else{
			$this->mode = 0;
			if(substr($url, 0, 5)=='/view' && is_numeric(substr($url, 6))){
				$this->index = substr($url, 6);
			}else{
				$this->index = 1;
			}
		}
	}
	
	private function link($link=''){
		return $_SERVER['SCRIPT_NAME'].'/'.$link;
	}
	
	private $records_per_page = 5;
	private function view(){
		if($this->where){
			$where = 'WHERE '.$this->where;
		}else{
			$where = '';
		}
		$query = 	'SELECT SQL_CALC_FOUND_ROWS `id`, '.$this->safe(implode(',', array_keys($this->columns))).' '.
				'FROM '.$this->table.' '.
				$where.' '.
				'LIMIT '.($this->index-1)*$this->records_per_page.', '.$this->records_per_page;
		$return = '<h2>Lista</h2><table>';
		
		// thead
		$names = array();
		foreach($this->columns as $desc){
			$names[] = $desc['name'];
		}
		
		$return .= '<thead><tr><td>'.implode('</td><td>', $names).'</td><td>akcje</td></tr></thead><tbody>';
		
		$results = $this->sql->query($query);
		while($row = $results->fetch_object()){
			$return .= '<tr>';
			
			foreach($this->columns as $column => $desc){
				if(isset($desc['type']) && is_array($desc['type'])){
					$return .= '<td>'.$desc['type'][$row->$column].'</td>';
				}else{
					$return .= '<td>'.$row->$column.'</td>';
				}
			}
			
			// dodaj możliwość usuwania/modyfikacji
			$return .= '<td><ul>';
			
			// usuwanie
				$return .= sprintf('<li><a href="%1$s">usuń</a></li>', $this->link('delete/'.$row->id));
			// modyfikacja
				$return .= sprintf('<li><a href="%1$s">edytuj</a></li>', $this->link('edit/'.$row->id));
			
			$return .= '</ul></td></tr>';
		}
		$return .= '</tbody></table>';
		
		/*
			lista zmieniaczy stron
		*/
		$result = $this->sql->query('SELECT FOUND_ROWS() as `count`');
		$count = $result->fetch_object()->count; # find how many doctors do we have
		$sites = ceil($count/$this->records_per_page);
		
		for($i = 1, $switch = '<ul class="switcher">'; $i <= $sites; $i++){
			if($this->index==$i){
				$switch .= '<li><a href="'.$this->link('view/'.$i).'"><strong>'.$i.'</strong></a></li>';
			}else{
				$switch .= '<li><a href="'.$this->link('view/'.$i).'">'.$i.'</a></li>';
			}
		}
		$switch .= '</ul>';
		/* koniec listy zmieniaczy */
		$return .= $switch;
		
		/* guzik dodawania */
		$return .= '<a href="'.$this->link('add').'">dodaj</a>';
		
		
		
		return $return;
	}
	
	private function edit(){
		$query =	'SELECT `id`, '.$this->safe(implode(',', array_keys($this->columns))).' '.
				'FROM '.$this->table.' '.
				'WHERE `id`='.$this->safe($this->index).' '.
				'LIMIT 1';
				
		$return = '<h2>Modyfikuj rekord</h2><form method="post" action="'.$this->link().'"><ul>';
		
		$results = $this->sql->query($query);
		$row = $results->fetch_object();
		
		foreach($this->columns as $column => $desc){
			if(!$desc['readonly']){
				$return .= sprintf('<li><label for="%3$s">%1$s</label>'.$this->input($column, $row->$column, $desc['type']).'</li>', $desc['name'],  $row->$column, $column);
			}
		}
		
		$return .= '</ul>';
		
		$return .= '<input type="hidden" name="index" value="'.$this->index.'" />';
		
		$return .= '<input type="submit" name="submit_edit" value="zapisz" /></form>';
		
		return $return;
	}
	
	private function input($name, $value, $type=null){
		if($type=='short' || !$type){
			return sprintf('<input type="text" name="%1$s" value="%2$s" />', $name, $value);
		}elseif($type=='long'){
			return sprintf('<textarea name="%1$s">%2$s</textarea>', $name, $value);
		}elseif(is_array($type)){
			$return = sprintf('<select name="%1$s">', $name);
			
			foreach($type as $option_value => $option_name){
				if($option_value == $value){
					$return .= sprintf('<option selected="selected" value="%1$s">%2$s</option>', $option_value, $option_name);
				}else{
					$return .= sprintf('<option value="%1$s">%2$s</option>', $option_value, $option_name);
				}
			}
			
			$return .= '</select>';
			
			return $return;
		}
	}
	
	private function delete(){
		$return = '<h2>Usuń rekord</h2><form method="post" action="'.$this->link().'"><ul>';
		
		$query =	'SELECT `id`, '.$this->safe(implode(',', array_keys($this->columns))).' '.
				'FROM '.$this->table.' '.
				'WHERE `id`='.$this->safe($this->index).' '.
				'LIMIT 1';
				
		$return = '<h2>Usuń rekord</h2><form method="post" action="'.$this->link().'"><ul>';
		
		$results = $this->sql->query($query);
		$row = $results->fetch_object();
		
		foreach($this->columns as $column => $desc){
			$return .= sprintf('<dt>%1$s</dt><dd>%2$s</dd>', $column,  $row->$column, $column);
		}
		
		$return .= '</ul>';
		
		$return .= '<input type="hidden" name="index" value="'.$this->index.'" />';
		
		$return .= '<input type="submit" name="submit_delete" value="usuń" /><a href="'.$this->link().'">anuluj</a></form>';
		
		return $return;
	}
	
	public function add(){
		$return = '<h2>Dodaj rekord</h2><form method="post" action="'.$this->link().'"><ul>';
		
		foreach($this->columns as $column => $desc){
			if(!$desc['readonly']){
				$return .= sprintf('<li><label for="%3$s">%1$s</label>'.$this->input($column, $row->$column, $desc['type']).'</li>', $desc['name'],  $row->$column, $column);
			}
		}
		
		$return .= '</ul>';
		
		$return .= '<input type="submit" name="submit_add" value="zapisz" /></form>';
		
		return $return;
	}
	
	public function __toString(){
		return $this->display();
	}
	
	public function display(){
		$return = '';
		switch($this->mode){
			case 0:
				$return = $this->view();
				break;
			case 1:
				$return = $this->edit();
				break;
			case 2:
				$return = $this->add();
				break;
			case 3:
				$return = $this->delete();
				break;
		}
		
		return '<div id="scaffold">'.$return.'</div>';
	}
}

?>