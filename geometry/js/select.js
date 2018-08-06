var Select = function(){}
Select.add = function(object){ console.log(object);
// TODO: zaznaczanie obiektów w dowolnej kolejności
	var add = false;
	switch(this.type){
		case 'line':
			if(object.line)
				add = true;
		break;
		case 'handle':
			if(object.handle)
				add = true;
		break;
		case 'circle':
			if(object.circle){
				add = true;
			}
		break;
		case 'any':
			if(object.node){
				add = true;
			}
		break;
	}
	if(add){
		this.objects[this.parentIndex] = object;
		ObjectsGUI.highlightSelected(this.parentIndex);
		this.setNext();
	}
}
Select.setNext = function(){
	for(var i = 0; i < this.parents.length; i++){
		if(!this.objects[i]){
			this.parentIndex = i;
			this.type = this.parents[i];
			break;
		}
	}
	if(i > this.parentIndex){
		ObjectsGUI.enableOk();
	}
}
Select.clean = function(){
	this.parents = [];
	this.objects = [];
	this.parentIndex = 0;
	this.able = false;
	this.object = 0;
	this.type = 0;
}
// dodaj obiekt jako obiekt do wyboru
Select.addObject = function(object){
	this.clean();
	this.able = true;
	this.parents = object.parentsType;
	this.setNext();
	this.object = object;
}
// uruchom tworzenie obiektu
Select.fire = function(){
	window[this.object.name].create(this.objects);
	this.clean();
	ObjectsGUI.off();
}