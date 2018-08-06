// okrąg środek + punkt
var CenterPointCircle = RootObject.extend({
	init: function(node){
		this._super(node);
		this.circle = new SvgCircle(this.node);
	},
	/* parents:
		0. center
		1. pointOnCircle
	*/
	update: function(){
		// calulations
		
		var p0 = this.parents[0].handle.point
		var p1 = this.parents[1].handle.point
		
		this.circle.c = p0;
		this.circle.r = p0.distanceFrom(p1);
		
		//console.log( this.object.c + ' ' + this.object.r)
		
		//$$('test').setAttribute('cx', this.object.c.x);
		//$$('test').setAttribute('cy', this.object.c.y);
		
		this.circle.update()
		
		this._super();
	}
});
CenterPointCircle.create = function(parents){
	var node = SvgCircle.create(parents[0].handle.point, 5);
	if(svg.objects.firstChild){
		svg.objects.insertBefore(node, svg.objects.firstChild);
	}else{
		svg.objects.appendChild(node);
	}
	
	node.setAttribute('type', 'CenterPointCircle');
	RootObject.create(node, parents);
	node.object.update();
}
ObjectsGUI.add(CenterPointCircle, 'CenterPointCircle', 'Okrąg (środek i pkt na okręgu)', ['handle', 'handle'], ['środek', 'pkt na okręgu']);

// okrąg 3 punkty
var ThreePointCircle = RootObject.extend({
	init: function(node){
		this._super(node);
		this.circle = new SvgCircle(this.node);
	},
	/* parents:
		0. point
		1. point
		2. point
	*/
	update: function(){
		// calulations
		
		var p1 = this.parents[0].handle.point
		var p2 = this.parents[1].handle.point
		var p3 = this.parents[2].handle.point
		
		// obliczenia za http://www.kevlindev.com/geometry/2D/three_point_circle.svg
		var ma = (p2.y - p1.y) / (p2.x - p1.x);
		var mb = (p3.y - p2.y) / (p3.x - p2.x);
	
		// NOTE: should check (mb - ma) == 0...points are co-linear
		var x =
			(ma * mb * (p1.y - p3.y) +
			mb * (p1.x + p2.x) -
			ma * (p2.x + p3.x)   ) / (2 * (mb - ma));
		var y = (-1 / ma) * (x - (p1.x + p2.x)/2) + (p1.y + p2.y)/2;
		
		var center = new Point2D(x, y);
	
		this.circle.c = center;
		this.circle.r = p1.distanceFrom(center);
		
		this.circle.update()
		
		this._super();
	}
});
ThreePointCircle.create = function(parents){
	var node = SvgCircle.create(parents[0].handle.point, 5);
	if(svg.objects.firstChild){
		svg.objects.insertBefore(node, svg.objects.firstChild);
	}else{
		svg.objects.appendChild(node);
	}
	
	node.setAttribute('type', 'ThreePointCircle');
	RootObject.create(node, parents);
	node.object.update();
}
ObjectsGUI.add(ThreePointCircle, 'ThreePointCircle', 'Okrąg 3 punktów', ['handle', 'handle', 'handle'], ['1 punkt', '2 punkt', '3 punkt']);

