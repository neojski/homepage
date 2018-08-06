// główny obiekt
// metoda trim
String.prototype.trim = function () {
    return this.replace(/^\s*/, '').replace(/\s*$/, '');
}

var RootObject = Class.extend({
	init: function(node){
		this.node = node;
		
		this.id = node.id;
		this.children = [];
		this.parents = [];
	},
	
	toString: function(){
		return this.id;
	},
	
	// WARNING ACHTUNG ATTENZIONE!!
	// super slow function
	/*getParentIndex: function(parent){
		for(var i = 0; i < this.parents.length; i++){
			if(this.parents[i].id == parent.id){
				return i;
			}
		}
		return -1;
	},*/
	
	// parents interface
	loadParents: function(){
		var parents_list = this.node.getAttribute('parents');
		if(parents_list){
			var parents_array = parents_list.trim().split(' ');
			for(var i = 0; i < parents_array.length; i++){
				this.loadParent(parents_array[i]);
			}
		}
	},
	loadParent: function(parentId){
		this.parents.push(ChooseObject($$(parentId)));
	},
	/*FIXME: popatrz na addChild
	addParent: function(parentId){
		this.loadParent(parentId);
		this.node.setAttribute('parents', (this.node.getAttribute('parents') || '') + ' ' + parentId);
	},*/
	
	// children interface 
	loadChildren: function(){
		var children_list = this.node.getAttribute('children');
		if(children_list){
			var children_array = children_list.trim().split(' ');
			for(var i = 0; i < children_array.length; i++){
				this.loadChild(children_array[i]);
			}
		}
	},
	loadChild: function(childId){
		this.children.push(ChooseObject($$(childId)));
	},
	addChild: function(childObject){
		this.children.push(childObject);
		var c = this.node.getAttribute('children');
		this.node.setAttribute('children', (c ? c + ' ' : '')  + childObject.id);
	},
	
	// should be replaced with one interface elements
	/*loadElements: function(elementType)samo{
		var elements = this.node.getAttribute(elementType);
		if(elements){
			var elements_array = elements.split(' ');
			var elements_array_length;
			for(var i = 0; i < elements_array_length; i++){
				this.loadElement(elementType);
			}
		}
	},*/
	
	// updating object - update all children
	update: function(){
		for(var i = 0, l = this.children.length; i < l; i++){
			this.children[i].update();
		}
	},
	// remove itself
	remove: function(dontFixParent){
		console.log('s '+this.id)
		
		// remove parents' children attribute (trim!) and reload children
		/*
			dontFixParent explanation:
			
			it's unnecessary to fix children's parent since it'll be removed
			what's more trying to fix parent makes problems with index in loop
		*/
		if(!dontFixParent)
		for(var i = 0; i < this.parents.length; i++){
			var parent = this.parents[i];
			
			parent.node.setAttribute(
				'children',
				(parent.node.getAttribute('children') + '').replace(this.id, '').replace(/\s+/g, ' ')
			);
			
			parent.children = [];
			parent.loadChildren();
		}
		
		
		
		// remove children
		console.log('children '+this.children.join(','));
		for(var i = 0; i < this.children.length; i++){
			var child = this.children[i];
			if(child.node.parentNode){ // sometimes when children are connected to many parents it could be removed earlier
				child.remove(true);
			}
		}
		
		//console.log(this.id + ' ' + this.node + ' ' + this.node.parentNode)
		
		// remove node
		
		console.log('będę usuwał ' + this.id);
		this.node.parentNode.removeChild(this.node);
		console.log('e '+this.id)
	}
});
RootObject.c = 0;
RootObject.generateId = function(){
	var prefix = 'object';
	if(!$$(prefix+RootObject.c)){
		return prefix+(RootObject.c++);
	}else{
		while($$(prefix+RootObject.c)){
			RootObject.c++;
			if(RootObject.c > 1000){
				console.log('To przekracza możliwości przeglądarki w generowaniu id');
				break;
			}
		}
		return prefix+RootObject.c;
	}
}
RootObject.create = function(node, parents){
	// ustaw id
	node.setAttribute('id', this.generateId());
	
	if(parents){
		// ustaw rodziców w atrybucie parents
		for(var i = 0, parents_list = []; i < parents.length; i++){
			parents_list.push(parents[i].id);
		}
		node.setAttribute('parents', parents_list.join(' '));
	}
	
	// odpal element
	node.object = new ChooseObject(node);
	
	// ustaw go rodzicom
	for(var i = 0; i < parents.length; i++){
		parents[i].addChild(node.object);
	}
	
	// wczytaj rodziców
	node.object.loadParents();
}

// stała oznaczająca najdłuższą linię mieszczącą się na ekranie (do symulacji prostych)
RootObject.maxLine = 944;
document.addEventListener('DOMContentLoaded', function(){
	var svg = document.getElementsByTagName('svg')[0];
	RootObject.maxLine = Math.ceil(Math.sqrt( Math.pow(+svg.getAttribute('width'), 2) + Math.pow(+svg.getAttribute('width'), 2)));
}, false);




// ułatwiacz obłsugi obiektów svg
var SvgLine = Class.extend({
	init: function(node){
		this.node = node;
		
		// TODO: użyć interfejsu node.x1.baseVal.value
		this.p1 = new Point2D( +this.node.getAttribute('x1'), +this.node.getAttribute('y1') );
		this.p2 = new Point2D( +this.node.getAttribute('x2'), +this.node.getAttribute('y2') );
	},
	update: function(){
		this.node.setAttribute('x1', this.p1.x);
		this.node.setAttribute('y1', this.p1.y);
		this.node.setAttribute('x2', this.p2.x);
		this.node.setAttribute('y2', this.p2.y);
	}
});
SvgLine.create = function(p1, p2){
	var node = dom.create('svg:line', {
		'x1': p1.x,
		'y1': p1.y,
		'x2': p2.x,
		'y2': p2.y,
		'style': 'stroke:' + $$('selectColor').value
	});
	return node;
}

var SvgCircle = Class.extend({
	init: function(node){
		this.node = node;
		
		this.c = new Point2D( +this.node.getAttribute('cx'), +this.node.getAttribute('cy') );
		this.r = +this.node.getAttribute('r');
	},
	update: function(){
		this.node.setAttribute('cx', this.c.x);
		this.node.setAttribute('cy', this.c.y);
		this.node.setAttribute('r', this.r);
	}
});
SvgCircle.create = function(p, r){
	var node = dom.create('svg:circle', {
		'cx': p.x,
		'cy': p.y,
		'r': r,
		'style': 'fill:' + $$('selectColor').value
	});
	return node;
}

var SvgText = Class.extend({
	init: function(node){
		this.node = node;
		
		this.point = new Point2D( +this.node.getAttribute('x'), +this.node.getAttribute('y') );
	},
	update: function(){
		this.node.setAttribute('x', this.point.x + 10);
		this.node.setAttribute('y', this.point.y + 10);
	}
});
SvgText.create = function(point, text){
	var text = dom.create('svg:text', text, {
		'x': point.x,
		'y': point.y
	});
	return text;
}

var SvgHandle = Class.extend({
	init: function(node){
		this.node = node;
		
		this.point = new Point2D( +this.node.getAttribute('cx'), +this.node.getAttribute('cy') );
	},
	update: function(){
		this.node.setAttribute('cx', this.point.x);
		this.node.setAttribute('cy', this.point.y);
	}
});
SvgHandle.create = function(p){
	var node = dom.create('svg:circle', {
		'r': 5,
		'cx': p.x,
		'cy': p.y
	});
	return node;
}