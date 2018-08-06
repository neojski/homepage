<?php
	header('content-type:application/xhtml+xml');
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:svg="http://www.w3.org/2000/svg">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<script type="text/javascript">
/* popatrzeć na EventHandler w http://kevlindev.com/gui/2D.js */
	<![CDATA[
	
	onload = function(){
		mouse = new Mouser();
		
		
		var circles = document.getElementsByTagName('circle');
		for(var i = 0; i < circles.length; i++){
			var circle = circles[i];
			
			new Handle(circle);
		}
		
		
		
	}


Function.prototype.inherits = function(parent){
	this.prototype = new parent();
	this.prototype.constructor = this;
};

function Mouser(){
	this.init();
	this.handles = [];
}
Mouser.prototype.init = function(){
	var _Mouser = this;
	this.svg = document.getElementById('svg');
	this.svg.addEventListener("mousemove", function(e){_Mouser.mousemove(e)}, false);
	
}
Mouser.prototype.mousemove = function(e){
	if(this.handles.length){
		for(var i = 0; i < this.handles.length; i++){
			var handle = this.handles[i];
			
			var x = e.pageX - this.svg.offsetLeft;
			var y = e.pageY - this.svg.offsetTop;
			
			handle.move(x, y);
		}
	}
	e.preventDefault()
	return false;
}
Mouser.prototype.register = function(handle){
	this.handles.push(handle);
}
Mouser.prototype.unregister = function(handle){
	var index = this.handleIndex(handle);
	if(index != -1){
		this.handles.splice(index, 1);
	}
}
Mouser.prototype.handleIndex = function(handle) {
	for(var i = 0; i < this.handles.length; i++){
		if(this.handles[i] === handle){
			return i;
		}
	}
};

Mouser.prototype.unregisterAll = function(){
	this.handles = [];
}

function ChooseObject(node){
	var type = 0;
	
	if(!node){
		console.log('Obiekt nie istnieje');
	}
	/*if(type = node.getAttribute('type')){
		
	}else*/if(1){
		switch(node.nodeName.toLowerCase()){
			case 'line':
				return new Line(node);
			break;
			case 'polygon':
				return new Polygon(node);
			break;
			case 'text':
				return new Text(node);
			break;
			case 'path':
				return new Path(node);
			break;
			default:
				console.log('chooseobject zwrócił, że obiekt nie istnieje');
			break;
		}
	}
}

function RootObject(){}
RootObject.prototype.getHandleIndex = function(handle){
	if(!this.handles){
		alert('Obiekt nie ma uchwytów');
	}
	for(var i = 0; i < this.handles.length; i++){
		if(this.handles[i] == handle.id){
			return i;
		}
	}
	return -1;
}

function Line(node){
	this.node = node;
	
	this.handles = node.getAttribute('handles').split(' ');
}
Line.inherits(RootObject);
Line.prototype.update = function(handle){
	var index = this.getHandleIndex(handle);
	switch(index){
		case 0:
			this.node.setAttribute('x1', handle.x);
			this.node.setAttribute('y1', handle.y);
		break;
		case 1:
			this.node.setAttribute('x2', handle.x);
			this.node.setAttribute('y2', handle.y);
		break;
	}
}

/*
	łuk, łuk ma dwa parametry w atrybucie d
		1. Mx,y
		2. Qx1,y1 x,y
*/
function Path(node){
	this.node = node;
	
	this.handles = node.getAttribute('handles').split(' ');
}
Path.inherits(RootObject);
Path.prototype.update = function(handle){
	var index = this.getHandleIndex(handle);
	
	var pathSeg = this.node.pathSegList.getItem(index > 1 ? 1 : index);
	
	switch(index){
		case 0:
		case 2:
			pathSeg.x = handle.x;
			pathSeg.y = handle.y;
		break;
		case 1:
			pathSeg.x1 = handle.x;
			pathSeg.y1 = handle.y;
		break;
	}
}


function Polygon(node){
	this.node = node;
	
	this.handles = node.getAttribute('handles').split(' ');
}
Polygon.inherits(RootObject);
Polygon.prototype.update = function(handle){
	var index = this.getHandleIndex(handle);
	
	var points = this.node.getAttribute('points').split(' ');
	points[index] = handle.x + ',' + handle.y;
	
	this.node.setAttribute('points', points.join(' '));
}

function Text(node){
	this.node = node;
	
	this.x = +node.getAttribute('x');
	this.y = +node.getAttribute('y');
	
	this.handles = node.getAttribute('handles').split(' ');
}
Text.inherits(RootObject);
Text.prototype.update = function(handle){
	var index = this.getHandleIndex(handle);
	
	switch(index){
		case 0:
			this.node.setAttribute('x', handle.x);
			this.node.setAttribute('y', handle.y);
			
			this.x = handle.x;
			this.y = handle.y;
		break;
		/*case 1:
			this.node.setAttribute('transform', 'rotate('+Math.atan((this.y - handle.y) / (this.x - handle.x)) * 180 / Math.PI+','+this.x+','+this.y+')');
		break;*/
	}
}



function Handle(node){
	var _handle = this;
	this.x = +node.getAttribute('cx');
	this.y = +node.getAttribute('cy');
	
	this.id = node.getAttribute('id');
	
	this.node = node;
	node.object = this;
	
	this.selected = false;
	
	this.owners = this.getOwners();
	
	node.addEventListener("mousedown", function(e){_handle.mousedown(e)}, false);
	node.addEventListener("mouseup", function(e){_handle.mouseup(e)}, false);
}
/* FIXME: nieoptymalne rozwiązanie, nie zawsze trzeba wczytywać od razu */
Handle.prototype.getOwners = function(){
	var owners_ids = this.node.getAttribute('owners').split(' ');
	for(var i = 0, owners = []; i < owners_ids.length; i++){
		var object = new ChooseObject(document.getElementById(owners_ids[i]));
		
		owners.push(object);
	}
	return owners;
}
Handle.prototype.move = function(x, y){
	this.node.setAttribute('cx', x);
	this.node.setAttribute('cy', y);
	
	this.x = x;
	this.y = y;
	
	for(var i = 0; i < this.owners.length; i++){
		//var object = new Object(this.owners[i]);
		this.owners[i].update(this);
	}
}
Handle.prototype.mousedown = function(e){
	if(!this.selected){
		this.selected = true;
		
		mouse.unregisterAll();
        	mouse.register(this);
	}
};
Handle.prototype.mouseup = function(e){
	if(this.selected){
		this.selected = false;
		
		mouse.unregisterAll();
        	mouse.unregister(this);
	}
};

	
	]]></script>
	
	<style type="text/css">
	#test{
		margin: 20px;
		border: 20px solid black;
		padding: 20px;
	}
	
	#inner{
		background: lightblue;
	}
	</style>
</head>
<body>
	<div id="test">
	
	<div id="svg">
	<svg xmlns="http://www.w3.org/2000/svg" width="800" height="200" style="border: 2px solid">
		<style type="text/css">
		circle{fill: #fff; stroke: #000; stroke-width: 2}
		line{stroke: #000; stroke-width: 2}
		
		line{stroke: #ff0000;}
		path{stroke: #ff0000; fill: none; stroke-width: 2}
		</style>
		
		<g type="arcs">
			<line x1="11" y1="55" x2="165" y2="135" id="l1" handles="a b"/>
			<path d="M11,55 Q165,134 478,64" id="p1" handles="a b c" />
			<line x1="165" y1="135" x2="478" y2="64" id="l2" handles="b c"/>
			<line x1="478" y1="64" x2="768" y2="147" id="l3" handles="c d"/>
		</g>
		
		
		<text x="50" y="20" font-size="20" id="t1" handles="e">test</text>
		
		<g id="handles">

			<circle cx="11" cy="55" r="3" style="" id="a" owners="l1 p1"/>
			<circle cx="165" cy="135" r="3" id="b" owners="l1 l2 p1"/>
			<circle cx="478" cy="64" r="3" id="c" owners="l2 l3 p1"/>
			<circle cx="768" cy="147" r="3" id="d" owners="l3"/>
			
			<circle cx="50" cy="20" r="3" id="e" owners="t1"/>
		</g>

	</svg>
	</div>
	
	</div>
</body>
</html>