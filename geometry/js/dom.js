// prosty, szybki interfejs tworzenia obiektów
dom = {
	ns:{
		'svg': 'http://www.w3.org/2000/svg',
		'xlink':  'http://www.w3.org/1999/xlink'
	},
	create:function(name, value, attributes){
		var i = name.indexOf(':');
		var node = 0;
		if(i > -1){
			var prefix	= name.slice(0, i);
			var name	= name.slice(i+1);
			var node = document.createElementNS(dom.ns[prefix], name);
		}else if(name){
			var node = document.createElement(name);
		}
		
		if(node){
			if(typeof(value) == 'object'){
				attributes = value
			}else{
				if(value){
					node.appendChild(document.createTextNode(value));
				}
			}
			if(attributes){
				for(var attribute in attributes){
					var j = attribute.indexOf(':');
					if(j > -1){
						var prefix	= attribute.slice(0, j);
						var attribute_v	= attribute.slice(j+1);
						
						node.setAttributeNS(
							dom.ns[prefix],
							attribute_v,
							attributes[attribute]
						);
					}else if(attribute){
						node.setAttribute(
							attribute,
							attributes[attribute]
						);
					}
				}
			}
		}else if(!attributes){
			var node = document.createTextNode(value);
		}else{
			return;
		}
		return node;
	}
}

/*
function create(name, attributes){
	var node = document.createElementNS(svg.ns, name);
	if(attributes){
		var i;
		for(i in attributes){
			node.setAttributeNS(null, i, attributes[i]);
		}
	}
	return node;
}
*/