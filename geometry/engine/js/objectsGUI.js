function ObjectsGUI(){};
ObjectsGUI.objects = [];
ObjectsGUI.opened = false;
ObjectsGUI.add = function(object, name, plname, parentsType, parentsDesc){
	// FIXME: what a mess!
	object.parentsType = parentsType
	object.parentsDesc = parentsDesc
	object.name = name
	object.plname = plname
	this.objects.push(object);
}
ObjectsGUI.render = function(){
	for(var i = 0; i < this.objects.length; i++){
		var li = dom.create('li', 0, {
			'title': this.objects[i].plname
		});
		
		var img = dom.create('img', 0, {
			'src': 'design/'+this.objects[i].name+'.png',
			'alt': this.objects[i].plname
		});
		
		li.appendChild(img);
		
		li.object = this.objects[i];
		
		$$('newObject').appendChild(li)

		$(li).click(function(){
			ObjectsGUI.click(this.object, this);
		});
	}
}
ObjectsGUI.click = function(object, objectLi){
	if(this.opened){
		this.off();
	}else{
		this.on(object, objectLi);
	}
}
ObjectsGUI.on = function(object, li){
	$(li).addClass('selected')
	Select.addObject(object);
	for(var i = 0; i < object.parentsType.length; i++){
		var li = dom.create('li', object.parentsDesc[i]);
		$$('selectObjects').appendChild(li);
		
		$('#objectName').html(object.plname);
	}
	$('#selected').show();
	this.opened = true;
}
ObjectsGUI.off = function(){
	$('li.selected').removeClass('selected'); // wyłącz wybrany obiekt
	Select.clean(); // wyczyść Select
	var o = $$('selectObjects'); // usuń wrzucone elementy, które należy zaznaczyć
	var l;
	while(l = o.lastChild){
		o.removeChild(l);
	}
	$('#selected').hide(); // schowaj
	this.opened = false; // ustaw status
	$('#okSelect').attr('disabled', true);
}
ObjectsGUI.highlightSelected = function(index){
	$('div#selected li:nth-child('+(index+1)+')').addClass('selected');
}
ObjectsGUI.enableOk = function(){
	$('#okSelect').removeAttr('disabled');
}

function notebook(dl){
	this.active = 0;
	
	var m = this;
	
	this.action = function(o){
		if(m.active){
			m.active.removeAttribute('class');
		}
		m.active = this;
		this.setAttribute('class', 'selected');
	}
	
	this.tab = function(name){
		var dt = document.createElement('dt');
		dt.appendChild(document.createTextNode(name));
		this.dl.appendChild(dt);
		
		var dd = document.createElement('dd');
		this.dl.appendChild(dd);
		
		dt.onclick = m.action
		
		return dd;
	}
	
	if(dl){
		this.dl = dl;
		for(var i=0, dt; dt=dl.getElementsByTagName('dt')[i++];){
			dt.onclick = this.action
		}
	}else{
		this.dl = document.createElement('dl');
	}
	
	this.dl.setAttribute('class', 'notebook');
}