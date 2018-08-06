// punkt przecięcia dwu prostych
var LinesIntersection = RootObject.extend({
	init: function(node){
		this._super(node);
		
		this.handle = new SvgHandle(node);
	},
	/* parents:
		0. line
		1. line
	*/
	update: function(){
		// calulations
		var l = this.parents[0].line;
		var r = this.parents[1].line;
		
		this.handle.point = Intersection.lineLinePoint( l.p1, l.p2, r.p1, r.p2 );
		
		this.handle.update()
		
		this._super();
	}
});
LinesIntersection.create = function(parents){
	var node = SvgHandle.create(parents[0].line.p1);
	svg.handles.appendChild(node);
	
	node.setAttribute('type', 'LinesIntersection');
	RootObject.create(node, parents);
	node.object.update();
}
ObjectsGUI.add(LinesIntersection, 'LinesIntersection', 'Pkt przecięcia dwu linii', ['line', 'line'], ['linia 1', 'linia 2']);

// środek między dwoma punktami
var Midpoint = RootObject.extend({
	init: function(node){
		this._super(node);
		
		this.handle = new SvgHandle(node);
	},
	/* parents:
		0. handle
		1. handle
	*/
	update: function(){
		// calulations
		var l = this.parents[0].handle.point;
		var r = this.parents[1].handle.point;
		
		this.handle.point = l.add(r).divide(2);
		
		this.handle.update()
		
		this._super();
	}
});
Midpoint.create = function(parents){
	var node = SvgHandle.create(parents[0].handle.point);
	svg.handles.appendChild(node);
	
	node.setAttribute('type', 'Midpoint');
	RootObject.create(node, parents);
	node.object.update();
}
ObjectsGUI.add(Midpoint, 'Midpoint', 'Środek "odcinka"', ['handle', 'handle'], ['punkt 1', 'punkt 2']);

// punkt symetryczny do danego względem linii
var ReflectionHandle = RootObject.extend({
	init: function(node){
		this._super(node);
		
		this.handle = new SvgHandle(node);
	},
	/* parents:
		0. handle
		1. line
	*/
	update: function(){
		// calulations
		var p = this.parents[0].handle.point;
		var l = this.parents[1].line;
		var p1 = l.p1;
		var p2 = l.p2;
		
		// obliczenia za http://www.kevlindev.com/geometry/2D/projection_reflection.svg
		var v1      = Vector2D.fromPoints(p1,p2);
		var v2      = Vector2D.fromPoints(p1, p);
		var percent = v2.dot(v1) / v1.dot(v1);
		//var proj    = p1.lerp(p2, percent);
		var refl    = v1.multiply(-2 * percent).add(v2);
	
		this.handle.point = p1.subtract(refl);

		this.handle.update()
		
		this._super();
	}
});
ReflectionHandle.create = function(parents){
	var node = SvgHandle.create(parents[0].handle.point);
	svg.handles.appendChild(node);
	
	node.setAttribute('type', 'ReflectionHandle');
	RootObject.create(node, parents);
	node.object.update();
}
ObjectsGUI.add(ReflectionHandle, 'ReflectionHandle', 'Odbicie punktu względem linii', ['handle', 'line'], ['odbijany punkt', 'linia']);

var Handle = RootObject.extend({
	init: function(node){
		this._super(node);
		
		this.handle = new SvgHandle(node);
		this.selected = false;
		
		var _handle = this;
		node.addEventListener('mousedown', this, false);
		node.addEventListener('mouseup', this, false);
	},
	handleEvent: function(e){
		this[e.type](e);
	},
	move: function(point){
		//svg.style.display = 'none';
		
		this.handle.point = point;
		this.handle.update();
		
		//svg.style.display = 'block'
		
		this.update();
	},
	mousedown: function(e){ console.log('kliknięto handle')
		//if(!this.selected){
		//	this.selected = true;
			
		//	mouse.unregisterAll();
			mouse.register(this);
		//}
	},
	mouseup: function(e){
		//if(this.selected){
		//	this.selected = false;
			
			mouse.unregisterAll();
		//	mouse.unregister(this);
		//}
	}
});
Handle.create = function(point){
	// create circle
	var node = SvgHandle.create(point);
	svg.handles.appendChild(node);
	
	// create object
	RootObject.create(node, []);
}


// punkt na linii
var onLineHandle = Handle.extend({
	init: function(node){
		this._super(node);
		
		this.handle = new SvgHandle(node);
		this.percent = +this.node.getAttribute('percent');
	},
	setPercent: function(percent){
		this.percent = percent;
		this.node.setAttribute('percent', percent.toFixed(2));
	},
	/* parents:
		0. line
	*/
	update: function(justUpdate){
		if(!justUpdate){
			var line = this.parents[0].line;
			var v1 = Vector2D.fromPoints(line.p1, line.p2);
			
			this.handle.point = line.p1.add(v1.multiply(this.percent));
		}
		
		this.handle.update()
		
		this._super();
	},
	calculate: function(point){
		var line = this.parents[0].line;
		
		var v1 = Vector2D.fromPoints(line.p1, line.p2);
		var v2 = Vector2D.fromPoints(line.p1, point);
		var percent = v2.dot(v1) / v1.dot(v1);
		var proj    = line.p1.lerp(line.p2, percent);
		
		this.setPercent(percent);
		
		return proj;
	},
	// ruchome - dostań punkt, zwróć przetworzony punkt
	move: function(point){
		this.handle.point = this.calculate(point);

		this.update(true);
	}
});
onLineHandle.create = function(parents){
	var node = SvgHandle.create(parents[0].line.p1);
	svg.handles.appendChild(node);
	
	node.setAttribute('percent', .5);
	
	node.setAttribute('type', 'onLineHandle');
	RootObject.create(node, parents);
	node.object.update();
}
ObjectsGUI.add(onLineHandle, 'onLineHandle', 'Punkt na linii', ['line'], ['linia do której punkt będzie "przyklejony"']);


// punkt na okręgu
var onCircleHandle = Handle.extend({
	init: function(node){
		this._super(node);
		
		this.handle = new SvgHandle(node);
		this.angle = +this.node.getAttribute('angle');
	},
	setAngle: function(angle){
		this.angle = angle;
		this.node.setAttribute('angle', angle.toFixed(2));
	},
	/* parents:
		0. line
	*/
	update: function(justUpdate){
		if(!justUpdate){
			var circle = this.parents[0].circle;
			
			this.handle.point.x = circle.c.x + circle.r * Math.cos(this.angle);
			this.handle.point.y = circle.c.y + circle.r * Math.sin(this.angle);
		}
		
		this.handle.update()
		
		this._super();
	},
	calculate: function(point){
		var circle = this.parents[0].circle;
		
		var angle = Math.atan((circle.c.y - point.y) / (circle.c.x - point.x));
		
		if(circle.c.x >= point.x){
			angle = angle - Math.PI; // dziwny zakres ze względu na odwróconą oś pionową
		}
		
		this.setAngle(angle);
	},
	// ruchome - dostań punkt, zwróć przetworzony punkt
	move: function(point){
		this.calculate(point);

		this.update();
	}
});
onCircleHandle.create = function(parents){
	var node = SvgHandle.create(parents[0].circle.c);
	svg.handles.appendChild(node);
	
	node.setAttribute('angle', 0);
	
	node.setAttribute('type', 'onCircleHandle');
	RootObject.create(node, parents);
	node.object.update();
}
ObjectsGUI.add(onCircleHandle, 'onCircleHandle', 'Punkt na okręgu', ['circle'], ['okrąg do którego punkt będzie "przyklejony"']);
