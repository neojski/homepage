<?php
/*
 *	xt templates system
 *	Copyright :(C) 2007 Tomasz 'neo' Kołodziejski <tkolodziejski at gmail dot com>, <neo007 at jabber dot com>
 *	E-mail    :tkolodziejski@gmail.com
 *	Website   :http://neo.mlodzi.pl/xt
 *	
 *	Convert *.ent files into simple php array.
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

$references='';
foreach(glob('./*.ent') as $ref){
	$references.=file_get_contents($ref);
}
preg_match_all('#<!ENTITY\s+([a-zA-z0-9]+).*?"(&\#[0-9]+;)"#s', $references, $matches);
$matches[1]=array_map(create_function('$a', 'return \'&\'.$a.\';\';'), $matches[1]);

$from = $matches[1];
$to = $matches[2];

$file = '<?php '.
'$entities_from = array( \''.
implode('\',\'', $matches[1]).
'\');'.

'$entities_to = array( \''.
implode('\',\'', $matches[2]).
'\');'.
'?>';

file_put_contents('entities.php', $file);
?>