onload = function(){
	var svg = document.getElementById('svg');
	
	var svgNS = 'http://www.w3.org/2000/svg';

	function d(name, value, attributes){
		var node = document.createElementNS(svgNS, name);
		if(node){
			if(value){
				node.appendChild(document.createTextNode(value));
			}
			if(attributes){
				for(var attribute in attributes){
					node.setAttribute(
						attribute,
						attributes[attribute]
					);
				}
			}
		}
		return node;
	}
	
	
	
	var krok = 1;
	document.getElementById('krok1').onclick = choose;
	
	// ustaw pewne stałe i zmienne
	var rects = 0; // pudełko na pokrycie
	var points = []; // punkty randomowe
	var len = 0; // jak duże ma być pokrycie
	var size;
	
	function choose(){
		len = Math.floor(1+Math.random() * 8); // randomowa wielkość pokrycia
		size = 100 / len + 100 / len / 2; // wielkość kwadracików pokryciowych
		points = []; // wyczyść z poprzedniego
		
		if(rects.parentNode){
			rects.parentNode.removeChild(rects);
		}
		rects = d('g')
		
		
		if(krok > 2) return;
		
		
		// tworzymy "losowe" pokrycie
		// ideologicznie: stwórz szachownicę cztery linie poziome, cztery pionowe plus trochę losowości
		for(var i = 0; i < len; i++){
			for(var j = 0; j < len; j++){
				points.push([
					Math.floor((100 / len / 2) + (100 / len) * i + (Math.random() - .5) * (100 / len / 2)),
					Math.floor((100 / len / 2) + (100 / len) * j + (Math.random() - .5) * (100 / len / 2))
				]);
			}
		}
		
		// dorzuć trochę zupełnie losowych
		for(var i = 0; i < len; i++){
			points.push([
				Math.floor(100 * Math.random()),
				Math.floor(100 * Math.random())
			]);
		}
		
		// narysuj pokrycie
		for(var i = 0, point; point = points[i++];){
			
			var rect = d('rect', 0, {
				x: point[0] - size/2,
				y: point[1] - size/2,
				width: size,
				height: size
			});
			
			rects.appendChild(rect);
		}
		svg.appendChild(rects);
		
		
		// strukturalne
		if(krok == 1){
			// pierwszoklik
			krok++;
			this.firstChild.nodeValue = 'wybierz lepsze pokrycie';
		}else{
			// zmień pokrycie
		}
	}
	
	// krok drugi: wybierz linię
	var x = 0;
	svg.onclick = function(e){
		if(krok != 2) return
		
		var zoom = (+svg.getAttribute('width')) / 100;
		// wybieramy elementy pokrycia, które są w "zasięgu myszy"
		x = (e.pageX - svg.parentNode.offsetLeft) / zoom;
		
		var line = d('line', 0, {
			x1: x,
			x2: x,
			y1: 0,
			y2: 100,
			class: 'verticalLine'
		});
		svg.appendChild(line);
		
		// zaciemnij i wyłącz nowe pokrycie
		document.getElementById('krok1').disabled = true;
		document.getElementById('krok2').style.color = '#999';
		krok++;
	}
	
	// krok3: wybierz elementy pokrywające linię
	document.getElementById('krok3').onclick = krok3
	var left = -Infinity; var right = +Infinity;
	function krok3(){
		if(krok == 2) alert('Postępuj zgodnie z opisem - kliknij rysunek');
		if(krok !=3) return;
		
		for(var chosen_points = [], rect = rects.firstChild; rect; rect = rect.nextSibling){
			if(Math.abs((+rect.getAttribute('x') + size / 2) - x) < size / 2){
				chosen_points.push(rect);
				
				rect.setAttribute('class', 'highlight');
				
				left = Math.max(left, +rect.getAttribute('x'));
				right = Math.min(right, (+rect.getAttribute('x')) + size);
			}
		}
		this.disabled = true;
		krok++;
	}
	
	document.getElementById('krok4').onclick = function(){
		if(krok != 4) return;
		
		// narysuj pasek, który się mieści
		var rect = d('rect', 0, {
			x: left,
			y: 0,
			width: right - left,
			height: 100,
			class: 'verticalRect'
		});
		svg.appendChild(rect);
		
		this.disabled = true;
		krok++;
	}
}


