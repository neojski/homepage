// zmianne globalne:
// mouse, svg

try{
	console = function(){}
	console.log = function(){}
}catch(e){

}


$$ = function(id){
	return document.getElementById(id);
}
document.addEventListener('DOMContentLoaded', function(){
	var objects = $('#handles > circle, #objects > *');
	
	// first: load objects
	for(var i = 0; i < objects.length; i++){
		var node = objects[i];
		node.object = new ChooseObject(node);
	}
	
	// second: load parents and children
	for(var i = 0; i < objects.length; i++){
		var node = objects[i];
		node.object.loadParents();
		node.object.loadChildren();
	}
	
	svg = document.getElementsByTagName('svg')[0];
	svg.objects = $$('objects')
	svg.handles = $$('handles')
	svg.ns = 'http://www.w3.org/2000/svg';
	
	ObjectsGUI.render();
	
	
	// załaduj na końcu, żeby nie było problemów z offsetami
	mouse = new Mouser();
}, false);

function ChooseObject(node){
	var type = 0;
	
	if(!node){
		console.log('Obiekt nie istnieje');
	}else{
	
	if(node.object){
		return node.object;
	}else{
		if(type = node.getAttribute('type')){
			return new window[type](node);
		}else{
			switch(node.nodeName.toLowerCase()){
				case 'circle':
					return new Handle(node);
				break;
				case 'line':
					return new Line(node);
				break;
				default:
					console.log('chooseobject zwrócił, że obiekt nie istnieje');
				break;
			}
		}
	}
	
	// debug
	console.log('ChooseObject niczego nie wybrał, otrzymał '+node);}
}
