function Mouser(){
	this.init();
	this.handles = [];
}
Mouser.prototype.init = function(){
	var _Mouser = this;
	this.svg = document.getElementById('svg');
	
	this.loadOffsets();
	
	document.addEventListener('mousemove', this, false);
	document.addEventListener('mouseup', this, false);
	document.addEventListener('drag', this, false);
	document.addEventListener('click', this, false);
}
Mouser.prototype.handleEvent = function(e){
	this[e.type](e);
}
var T = 0;
// wrzuć dopiero po załadowaniu i wyrenderowaniu dokumentu
Mouser.prototype.loadOffsets = function(){
	this.offsetLeft = this.svg.parentNode.offsetLeft;
	this.offsetTop = this.svg.parentNode.offsetTop;
}
Mouser.prototype.mousemove = function(e){
	if(!T){
		var _Mouser = this;
		T = setTimeout(function(){_Mouser.mousemoveRun(e)}, 1);
	}
	e.preventDefault();
	return false;
}
Mouser.prototype.drag = function(e){
	e.preventDefault();
	return false;
}
Mouser.prototype.click = function(e){
	// utwórz kółka
	if(e.ctrlKey){
		Handle.create(this.getPoint(e));
	}
	
	console.log('Kliknięto '+e.target.nodeName);
	
	if(e.target.object){
		var object = e.target.object;
		
		if(Select.able){
			Select.add(object);
		}
	}
}
Mouser.prototype.getPoint = function(e){
	return new Point2D(
		e.pageX - this.offsetLeft,
		e.pageY - this.offsetTop
	);
}
Mouser.prototype.mousemoveRun = function(e){
	clearTimeout(T);
	T = 0;
	/*if(this.handles.length){
		for(var i = 0; i < this.handles.length; i++){
			var handle = this.handles[i];
			
			var x = e.pageX - this.svg.offsetLeft;
			var y = e.pageY - this.svg.offsetTop;
			
			handle.move(new Point2D(x, y));
		}
	}*/
	if(this.handle){
		this.handle.move(this.getPoint(e));
	}
}
Mouser.prototype.mouseup = function(e){
	this.unregisterAll();
}

Mouser.prototype.register = function(handle){
	this.handle = handle;
}
Mouser.prototype.unregisterAll = function(){
	this.handle = 0;
}
/*Mouser.prototype.register = function(handle){
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
}*/