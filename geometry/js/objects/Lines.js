// zwykła linia
var Line = RootObject.extend({
	init: function(node){
		this._super(node);
		
		this.line = new SvgLine(node);
	},
	update: function(){
		this.line.p1 = this.parents[0].handle.point;
		this.line.p2 = this.parents[1].handle.point;
		
		this.line.update();
		
		this._super();
	}
});
Line.create = function(parents){
	var node = SvgLine.create(parents[0].handle.point, parents[1].handle.point);
	svg.objects.appendChild(node);
	
	RootObject.create(node, parents);
}
ObjectsGUI.add(Line, 'Line', 'Odcinek', ['handle', 'handle'], ['początek odcinka', 'koniec odcinka']);



// linia prostopadła
var PerpendicularLine = RootObject.extend({
	init: function(node){
		this._super(node);
		
		this.line = new SvgLine(this.node);
	},
	/* parents:
		0. handle
		1. line
	*/
	update: function(){
		var ref_line = this.parents[1].line;
		var handle = this.parents[0].handle.point;
		
		// Calculation after
		// http://www.kevlindev.com/geometry/2D/projection_reflection.svg
		var p1      = ref_line.p1;
		var p2      = ref_line.p2;
		var v1      = Vector2D.fromPoints(p1,p2);
		var v2      = Vector2D.fromPoints(p1, handle);
		var percent = v2.dot(v1) / v1.dot(v1);
		var proj    = p1.lerp(p2, percent);
		
		// apply calculation
		this.line.p1 = proj;
		this.line.p2 = handle;
		
		this.line.update()
		
		this._super();
	}
});
PerpendicularLine.create = function(parents){
	var node = SvgLine.create(parents[0].handle.point, parents[0].handle.point);
	svg.objects.appendChild(node);
	
	node.setAttribute('type', 'PerpendicularLine');
	RootObject.create(node, parents);
	node.object.update();
}
ObjectsGUI.add(PerpendicularLine, 'PerpendicularLine', 'Wysokość', ['handle', 'line'], ['punkt z którego opuszczamy wysokość', 'odcinek na który opuszczamy wysokość']);

// dwusieczna
var AngleBisector = RootObject.extend({
	init: function(node){
		this._super(node);
		
		this.line = new SvgLine(this.node);
	},
	/* parents:
		0. line
		1. line
	*/
	update: function(){
		var l = this.parents[0].line;
		var r = this.parents[1].line;
		
		// calulations
		// znajdź wspólny wierzchołek
		// TODO: wpsólny wierzchołek można wyznaczyć na starcie zamiast zawsze go liczyć
		var w;
		if(l.p1.equals(r.p1) || l.p1.equals(r.p2)){
			w = l.p1;
		}else{
			w = l.p2;
		}
		
		// utwórz wektory w dobrym kierunku, czyli od wspólnego wierzchołka
		var v1 = Vector2D.fromPoints(w, w.equals(l.p2) ? l.p1 : l.p2);
		var v2 = Vector2D.fromPoints(w, w.equals(r.p2) ? r.p1 : r.p2);
		
		//console.log(v1 + ' ' + v2);
		
		/* algorytm
			Tworzymy dwa wektory z podanych linii, jeden przemnażamy tak, aby jego długość była taka jak drugiego, wektory sumujemy, otrzymując odpowiedni punkt
		*/
		v2.multiplyEquals( v1.length() / v2.length() );
		
		
		//console.log( l.p1 + ' ' + l.p2 + '   ' + r.p1 + ' ' + r.p2 + ' w ' + w);
		
		var v3 = v2.add(v1);
		
		// wyrzuć koniec za ekran
		v3.multiplyEquals(RootObject.maxLine / v3.length())
		
		this.line.p1 = w;
		this.line.p2 = w.add(v3);
		
		//$$('test').setAttribute('cx', v2.add(l.p1).x);
		//$$('test').setAttribute('cy', v2.add(l.p1).y);
		
		
		this.line.update()
		
		this._super();
	}
});
AngleBisector.create = function(parents){
	var node = SvgLine.create(parents[0].line.p1, parents[0].line.p2);
	svg.objects.appendChild(node);
	
	node.setAttribute('type', 'AngleBisector');
	RootObject.create(node, parents);
	node.object.update();
}
ObjectsGUI.add(AngleBisector, 'AngleBisector', 'Dwusieczna kąta wyznaczonego przez dwa odcinki o wspólnym początku', ['line', 'line'], ['pierwszy odcinek', 'drugi odcinek']);

// dwusieczna kąta wyznaczonego przez 3 punkty
var ThreePointAngleBisector = RootObject.extend({
	init: function(node){
		this._super(node);
		
		this.line = new SvgLine(this.node);
	},
	/* parents:
		0. line
		1. line
	*/
	update: function(){
		var p1 = this.parents[0].handle.point;
		var w = this.parents[1].handle.point;
		var p2 = this.parents[2].handle.point;
		
		// utwórz wektory
		var v1 = Vector2D.fromPoints(w, p1);
		var v2 = Vector2D.fromPoints(w, p2);
		
		/* algorytm
			Tworzymy dwa wektory z podanych linii, jeden przemnażamy tak, aby jego długość była taka jak drugiego, wektory sumujemy, otrzymując odpowiedni punkt
		*/
		v2.multiplyEquals( v1.length() / v2.length() );
		
		
		var v3 = v2.add(v1);
		
		// wyrzuć koniec za ekran
		v3.multiplyEquals(RootObject.maxLine / v3.length())
		
		this.line.p1 = w;
		this.line.p2 = w.add(v3);
		
		//$$('test').setAttribute('cx', v2.add(l.p1).x);
		//$$('test').setAttribute('cy', v2.add(l.p1).y);
		
		
		this.line.update()
		
		this._super();
	}
});
ThreePointAngleBisector.create = function(parents){
	var node = SvgLine.create(parents[0].handle.point, parents[0].handle.point);
	svg.objects.appendChild(node);
	
	node.setAttribute('type', 'ThreePointAngleBisector');
	RootObject.create(node, parents);
	node.object.update();
}
ObjectsGUI.add(ThreePointAngleBisector, 'ThreePointAngleBisector', 'Dwusieczna kąta wyznaczonego przez 3 punkty', ['handle', 'handle', 'handle'], ['punkt na jednym ramieniu', 'wierzchołek kąta', 'punkt na drugim ramieniu']);


// symetralna
var LineBisector = RootObject.extend({
	init: function(node){
		this._super(node);
		this.line = new SvgLine(this.node);
	},
	/* parents:
		0. line
	*/
	update: function(){
		var l = new SvgLine(this.parents[0].node);
		
		// calulations
		/* algorytm
			Bierzemy wektor prostopadły do wektora należącego do linii
			znajdujemy środek odcinka
		*/
		var v = Vector2D.fromPoints(l.p2, l.p1);
		
		// pseudoprosta
		var perp = v.perp().multiply(RootObject.maxLine / v.length());
		
		// środek odcinka
		var c = l.p2.add(l.p1).divide(2);
		
		// przesuń o prostopadłą w jedną i drugą stronę
		this.line.p1 = c.add(perp);
		this.line.p2 = c.subtract(perp);
		
		//$$('test').setAttribute('cx', v2.add(l.p1).x);
		//$$('test').setAttribute('cy', v2.add(l.p1).y);
		
		
		this.line.update()
		
		this._super();
	}
});
LineBisector.create = function(parents){
	var node = SvgLine.create(parents[0].line.p1, parents[0].line.p2);
	svg.objects.appendChild(node);
	
	node.setAttribute('type', 'LineBisector');
	RootObject.create(node, parents);
	node.object.update();
}
ObjectsGUI.add(LineBisector, 'LineBisector', 'Symetralna', ['line'], ['odcinek, do którego wystawimy symetralną']);

// linia równoległa przez punkt
var ParallelLine = RootObject.extend({
	init: function(node){
		this._super(node);
		
		this.line = new SvgLine(this.node);
	},
	/* parents:
		0. handle
		1. line
	*/
	update: function(){
		var line = this.parents[1].line;
		var point = this.parents[0].handle.point;
		
		//calculate
		var p1 = line.p1;
		var p2 = line.p2;
		var v1 = Vector2D.fromPoints(p1, p2);
		var v2 = Vector2D.fromPoints(p1, point);
		
		// pseudo prosta
		v1.multiplyEquals(RootObject.maxLine / v1.length());
		
		var s = p1.add(v2).add(v1);
		var e = p1.add(v2).subtract(v1);
		
		// apply calculation
		this.line.p1 = s;
		this.line.p2 = e;
		
		this.line.update()
		
		this._super();
	}
});
ParallelLine.create = function(parents){
	var node = SvgLine.create(parents[0].handle.point, parents[0].handle.point);
	svg.objects.appendChild(node);
	
	node.setAttribute('type', 'ParallelLine');
	RootObject.create(node, parents);
	node.object.update();
}
ObjectsGUI.add(ParallelLine, 'ParallelLine', 'Prosta równoległa', ['handle', 'line'], ['punkt przez który poprowadzić prostą', 'Odcinek, do którego ma być równoległa nowa prosta']);


// linia pod pewnym kątem
var AngleLine = RootObject.extend({
	init: function(node){
		this._super(node);
		
		this.angle = +node.getAttribute('angle') * Math.PI / 180;
		
		this.line = new SvgLine(node);
	},
	/* parents:
		0. line
		1. line
	*/
	update: function(){
		var p1 = this.parents[0].handle.point;
		var p2 = this.parents[1].handle.point;
		
		/* algorytm
			Przesuwamy środek do początku układu współrzędnych, wykonujemy obrót, przesuwamy na miejsce
		*/
		var p = p1.subtract(p2);
		p.multiplyEquals( RootObject.maxLine / Math.sqrt(p.x * p.x + p.y * p.y) );
		
		var p1 = p.clone();
		
		p.x = p1.x * Math.cos(this.angle) - p1.y * Math.sin(this.angle);
		p.y = p1.x * Math.sin(this.angle) + p1.y * Math.cos(this.angle);
		
		p.addEquals(p2);
		
		// wyrzuć koniec za ekran
		//v3.multiplyEquals(RootObject.maxLine / v3.length())
		
		this.line.p1 = p;
		this.line.p2 = p2;
		
		//$$('test').setAttribute('cx', v2.add(l.p1).x);
		//$$('test').setAttribute('cy', v2.add(l.p1).y);
		
		
		this.line.update()
		
		this._super();
	}
});
AngleLine.create = function(parents){
	var angle = +prompt('Podaj kąt w stopniach', 60);
	if(!isNaN(angle)){
		var node = SvgLine.create(parents[0].handle.point, parents[0].handle.point);
		svg.objects.appendChild(node);
		
		node.setAttribute('angle', angle * (-1));
		
		node.setAttribute('type', 'AngleLine');
		RootObject.create(node, parents);
		node.object.update();
	}else{
		alert('Nie podano poprawnej wartości liczbowej, nie narysuję.');
		return false;
	}
}
ObjectsGUI.add(AngleLine, 'AngleLine', 'Półprosta pod zadanym kątem', ['handle', 'handle'], ['punkt', 'środek obrotu']);

