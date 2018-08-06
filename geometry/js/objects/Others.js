// etykietki
var Label = RootObject.extend({
	init: function(node){
		this._super(node);
		
		this.text = new SvgText(node);
	},
	update: function(){
		this.text.point = this.parents[0].handle.point;
		
		this.text.update();
		
		this._super();
	}
});
Label.create = function(parents){
	var node = SvgText.create(parents[0].handle.point, prompt('Wpisz opis punktu'));
	svg.objects.appendChild(node);
	
	node.setAttribute('type', 'Label');
	RootObject.create(node, parents);
	node.object.update();
}
ObjectsGUI.add(Label, 'Label', 'Etykietki', ['handle'], ['punkt, do którego przykleić etykietkę']);

// usuwacz
var Delete = RootObject.extend({
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
Delete.create = function(parents){
	var parent = parents[0];
	if(confirm('Czy na pewno chcesz usunać wybrany obiekt i wszystkie z nim związane?')){
		parent.remove();
	}
}
ObjectsGUI.add(Delete, 'Delete', 'Usuwacz obiektów', ['any'], ['obiekt do usunięcia']);