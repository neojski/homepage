/**
 * list of object always sorted "getX"
 * creating and maintaining references called previous/next
 */
function NPList(){
	
}
NPList.prototype = new Array;
NPList.prototype.constructor = NPList;
/**
 * abstract function to return x
 */
NPList.prototype.getX = function(object){
	return object.x;
}
/**
 * returns index of object
 */
NPList.prototype.index = function(object){
	for(var i = 0; i < this.length; i++){
		if(this[i] == object){
			return i;
		}
	}
	return -1;
}
/**
 * get last object which getX <= x or return null
 */
NPList.prototype.getLeft = function(x){
	if(this.length == 0){
		return null;
	}
	if(this.getX(this[0]) > x){
		return null;
	}
	for(var i = this.length - 1; i >= 0; i--){
		if(this.getX(this[i]) <= x){
			return this[i];
		}
	}
}
/**
 * get fist object which getX >= x or return null
 */
NPList.prototype.getRight = function(x){
	if(this.length == 0){
		return null;
	}
	if(this.getX(this[this.length - 1]) < x){
		return null;
	}
	for(var i = 0; i < this.length; i++){
		if(this.getX(this[i]) >= x){
			return this[i];
		}
	}
}
/**
 * HACK: specific code
 * adds object after specified one + in DOM
 */
NPList.prototype.insertAfter = function(newObject, refObject){
	var index = this.index(refObject);
	this.splice(index + 1, 0, newObject);
	
	if(refObject.next){
		newObject.next = refObject.next;
		newObject.next.previous = newObject;
	}else{
		newObject.next = null;
	}
	refObject.next = newObject;
	newObject.previous = refObject;
	
	$(newObject.svgNode).insertAfter(refObject.svgNode);
}
/**
 * HACK: specific code
 * adds object before specified one + in DOM
 */
NPList.prototype.insertBefore = function(newObject, refObject){
	var index = this.index(refObject);
	this.splice(index, 0, newObject);
	
	if(refObject.previous){
		newObject.previous = refObject.previous;
		newObject.previous.next = newObject;
	}
	refObject.previous = newObject;
	newObject.next = refObject;
	
	$(newObject.svgNode).insertBefore(refObject.svgNode);
}
/**
 * removes object
 */
NPList.prototype.remove = function(object){
	var index = this.index(object);
	if(index > -1){
		if(this[index + 1] && this[index - 1]){
			this[index + 1].previous = this[index - 1];
			this[index - 1].next = this[index + 1];
		}else if(this[index + 1]){
			this[index + 1].previous = null;
		}else if(this[index - 1]){
			this[index - 1].next = null;
		}
		this.splice(index, 1);
	}
}
/**
 * adds object to the end
 */
NPList.prototype.add = function(object){
	object.previous = null;
	object.next = null;
	if(this.length > 0){
		this[this.length - 1].next = object;
		object.previous = this[this.length - 1];
	}
	this.push(object);
}
/**
 * tests references
 */
NPList.prototype.testRefs = function(){
	var correct = true;
	for(var i = 0; i < this.length; i++){
		var current = this[i];
		var next = this[i + 1];
		var previous = this[i - 1];
		if(next){
			if(current.next === next && next.previous === current){
				// ok
			}else{
				correct = false;
				log('next or next.previous', current)
			}
		}else{
			if(current.next === null){
				// ok
			}else{
				correct = false;
				log('next should be null',current)
			}
		}
		if(previous){
			if(current.previous === previous && previous.next === current){
				// ok
			}else{
				log('previous or previous.next', current);
				correct = false;
			}
		}else{
			if(current.previous === null){
				// ok
			}else{
				log('previous should be null', current);
				correct = false;
			}
		}
	}
	if(correct === false){
		console.log('błąd');
	}else{
		console.log('ok');
	}
}

// list = new NPList();
// var a = {x: 0, 'name': 'a'};
// var b = {x: 10, 'name': 'b'};
// var c = {x: 10, 'name': 'c'};
// var d = {x: 20, 'name': 'd'};
// list.add(a);
// list.add(b);
// list.add(c);
// list.add(d);
// // list.testRefs();
// 
// list.remove(d);
// list.remove(a);
// list.remove(c);
// list.remove(b);
// 
// alert(list[0]); // firefox bug https://bugzilla.mozilla.org/show_bug.cgi?id=675164