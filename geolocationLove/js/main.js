// Geolocation WebGL demo written by Tomasz Kołodziejski
// 
// Special thanks to the following:
//
// This file is inpired by http://code.google.com/p/webgl-globe/source/browse/globe/globe.js
//   Copyright 2011 Data Arts Team, Google Creative Lab
//  
//   Licensed under the Apache License, Version 2.0 (the 'License');
//   you may not use this file except in compliance with the License.
//   You may obtain a copy of the License at
//  
//   http://www.apache.org/licenses/LICENSE-2.0
// As well as by https://github.com/mrdoob/three.js/blob/master/examples/webgl_trackballcamera_earth.html
// I use here THREE.js library: https://github.com/mrdoob/three.js
// Also TWEEN.js: https://github.com/sole/tween.js/
// And jquery: http://jquery.org/ 

(function(){
var radius = 6371,
starScale = 0.1,
starRadius = radius * 2,

height = window.innerHeight,
width  = window.innerWidth,

container, stats,

boxDiv,

camera, scene, renderer, effectBloom, composer,
geometry, meshPlanet, meshStar, meshAtmo,

lovePointCoords = [],

myCoords,
fromCoords = null,

clock = new THREE.Clock(),

atmoSize = 1.1,

url = 'http://neo.infeo.pl/loveCounter.php',

requestAnimationFrameNeeded = true,
 
cameraDist, animFlag, options, options2, animLength, cameraFinishOffset;

// math functions
function coordsToVector3(coords){
	var lat = coords.latitude * Math.PI / 180, lon = coords.longitude * Math.PI / 180;
	return new THREE.Vector3(Math.cos(lat) * radius * Math.sin(lon),  Math.sin(lat) * radius, Math.cos(lat) * radius * Math.cos(lon));
}

function earthDist(c1, c2){
	var v1 = coordsToVector3(c1), v2 = coordsToVector3(c2);
	return Math.acos(v1.normalize().dot(v2.normalize())) * radius;
}

// get love points and message (done async during load)
$.ajax({
	type: 'GET',
	url: url,
	async: false,
	jsonpCallback: 'jsonCallback',
	contentType: "application/json",
	dataType: 'jsonp',
	data:{
		id: parent.location.search.slice(1) // cut ? off
	},
	success: function(json) {
		var from = json[0];
		lovePointCoords = json[1];
		
		if(from !== null){
			if(from[2] !== null && from[3] !== null){
				fromCoords = {latitude: from[2], longitude: from[3]};
			}
			if(from[1].length > 0){
				$(document).ready(function(){
					$('#message').text(from[1]);
				});
			}
		}
	},
	error: function(e) {
		console.log(e.message);
	}
});

// add message and get link
function addLoveMessage(coords, message){
	$.ajax({
		type: 'GET',
		url: url,
		async: false,
		jsonpCallback: 'jsonCallback',
		data: {
			latitude: Math.round(coords.latitude * 100) / 100, // no I won't store your exact location
			longitude: Math.round(coords.longitude * 100) / 100,
			random: !!coords.random,
			message: message
		},
		contentType: "application/json",
		dataType: 'jsonp',
		success: function(id) {
			$('#shareBox').fadeOut(1000, function(){
				var loc = parent.location, href = loc.href.slice(0, loc.href.lastIndexOf(loc.search)) + '?' + id;
				$(this).html('<a href="' +  href + '">' + href + '</a>').fadeIn(1000);
			});
		},
		error: function() {}
	});
}

// add your location to love points
function addLovePoint(coords){
	$.ajax({
		type: 'GET',
		url: url,
		jsonpCallback: 'none',
		data: {
			latitude: Math.round(coords.latitude * 100) / 100, // no I won't store your exact location
			longitude: Math.round(coords.longitude * 100) / 100
		},
		contentType: "application/json",
		dataType: 'jsonp',
		success: function() {},
		error: function() {}
	});
}

cameraDist = radius * 7;
animFlag = false;
options = {
	cameraLon: 0,
	cameraLat: 0,
	starLon: 0,
	starLat: 0,
	starRadius: radius * 2,
	atmoSize: 1.1,
	bloomStrength: 0,
	cameraY: 0,
	time: 0
};
options2 = {
	opacity: 0
};
animLength = 5000;
cameraFinishOffset = 0.3; // don't finish simple center
function startAnim(){
	var target;
	
	if(!myCoords.random && fromCoords){
		$('#dist').text('This message travelled to you '+earthDist(myCoords, fromCoords).toFixed(2)+'km');
	}

	options.starLon = (options.cameraLon + Math.PI) * 180 / Math.PI;
	
	target = {
		cameraLon: myCoords.longitude * Math.PI / 180 + Math.PI * 4 + cameraFinishOffset * Math.random(),
		cameraLat: myCoords.latitude * Math.PI / 180 + cameraFinishOffset * Math.random(), 
		starLon: myCoords.longitude + Math.PI * 2,
		starLat: myCoords.latitude,
		starRadius: radius,
		atmoSize: 6,
		bloomStrength: 3,
		time: 1
	};

	new TWEEN.Tween(options).to(target, animLength).easing(TWEEN.Easing.Quadratic.InOut).start();
	new TWEEN.Tween(options2).to({opacity: 1}, animLength * 0.2).delay(animLength * 0.8).easing(TWEEN.Easing.Quadratic.InOut).start();
	
	animFlag = true;
}

function render() {
	var delta = clock.getDelta();
	if(!animFlag){
		if(options.cameraLon > 2 * Math.PI){
			options.cameraLon -= 2 * Math.PI;
		}
		options.cameraLon += 0.1 * delta;
		
		meshStar.position.z = Math.cos(options.cameraLon + Math.PI) * options.starRadius;
		meshStar.position.x = Math.sin(options.cameraLon + Math.PI) * options.starRadius;
	}else{
		// animate cameraLon to myCoords angle
		TWEEN.update();
		
		meshAtmo.scale.set(options.atmoSize, options.atmoSize, options.atmoSize);
		meshAtmo.needsUpdate = true;
		
		meshStar.position = coordsToVector3({longitude: options.starLon, latitude: options.starLat}).normalize().multiplyScalar(options.starRadius);
		
		effectBloom.screenUniforms.opacity.value = options.bloomStrength;
		
		if(options.time > 0.8){
			boxDiv.style.opacity = options2.opacity;
		}
		if(options.time >= 1){
			requestAnimationFrameNeeded = false;
		}
	}
	
	camera.position.x = Math.cos(options.cameraLat) * Math.sin(options.cameraLon) * cameraDist;
	camera.position.z = Math.cos(options.cameraLat) * Math.cos(options.cameraLon) * cameraDist;
	camera.position.y = Math.sin(options.cameraLat) * cameraDist;
	
	camera.lookAt(new THREE.Vector3());
	
	renderer.clear();
	composer.render();
}

function animate() {
	if(requestAnimationFrameNeeded){
		requestAnimationFrame( animate );
	}

	render();
	stats.update();
}

function onWindowResize() {
	width = window.innerWidth;
	height = window.innerHeight;

	renderer.setSize( width, height );

	camera.aspect = width / height;		
	camera.updateProjectionMatrix();
	
	render();
}

function init() {
	var texture, material, stars, starsMaterials, starsGeometry, i, vertex, s,
	lovePointsMaterial, lovePointsGeometry, loveParticleSystem,
	starTexture, materialStar,
	renderModel, effectScreen;

	container = document.getElementById( 'container' );

	scene = new THREE.Scene();

	renderer = new THREE.WebGLRenderer( { clearAlpha: 1, clearColor: 0x000000, antialias: true } );
	renderer.setSize( width, height );
	renderer.sortObjects = false;
	renderer.autoClear = false;
	renderer.gammaInput = true;
	renderer.gammaOutput = true;

	container.appendChild( renderer.domElement );

	camera = new THREE.PerspectiveCamera( 25, width / height, 50, 1e7 );
	camera.position.z = radius * 7;

	geometry = new THREE.SphereGeometry( radius, 100, 50 );
	geometry.computeTangents();

	// atmosphere
	material = new THREE.ShaderMaterial({
		vertexShader: $('#atmosphereVertex').text(),
		fragmentShader: $('#atmosphereFragment').text(),
		transparent: true,
		side: THREE.BackSide
	});

	meshAtmo = new THREE.Mesh( geometry, material);
	meshAtmo.scale.set(atmoSize, atmoSize, atmoSize);

	// planet
	texture = THREE.ImageUtils.loadTexture( "textures/world.jpg" );
	material = new THREE.ShaderMaterial({
		uniforms: {
			'texture': { 
				type: 't',
				value: texture
			}
		},
		vertexShader: $('#earthVertex').text(),
		fragmentShader: $('#earthFragment').text()
	});
	
	meshPlanet = new THREE.Mesh( geometry, material );
	meshPlanet.rotation.y = - Math.PI / 2;
	
	scene.add( meshPlanet );
	scene.add(meshAtmo);

	// stars
	starsGeometry = new THREE.Geometry();
	for ( i = 0; i < 1500; i ++ ) {
		vertex = new THREE.Vector3();
		vertex.x = Math.random() * 2 - 1;
		vertex.y = Math.random() * 2 - 1;
		vertex.z = Math.random() * 2 - 1;
		vertex.multiplyScalar( radius );
		
		starsGeometry.vertices.push( vertex );
	}
	
	starsMaterials = [
		new THREE.ParticleBasicMaterial( { color: 0x555555, size: 2, sizeAttenuation: false } ),
		new THREE.ParticleBasicMaterial( { color: 0x555555, size: 1, sizeAttenuation: false } ),
		new THREE.ParticleBasicMaterial( { color: 0x333333, size: 2, sizeAttenuation: false } ),
		new THREE.ParticleBasicMaterial( { color: 0x3a3a3a, size: 1, sizeAttenuation: false } ),
		new THREE.ParticleBasicMaterial( { color: 0x1a1a1a, size: 2, sizeAttenuation: false } ),
		new THREE.ParticleBasicMaterial( { color: 0x1a1a1a, size: 1, sizeAttenuation: false } )
	];
	
	for ( i = 0; i < 10; i++ ) {
		stars = new THREE.ParticleSystem( starsGeometry, starsMaterials[i % 6]);

		stars.rotation.x = Math.random() * 6;
		stars.rotation.y = Math.random() * 6;
		stars.rotation.z = Math.random() * 6;

		s = (10 + i) * 20;
		stars.scale.set( s, s, s );

		stars.matrixAutoUpdate = false;
		stars.updateMatrix();

		scene.add( stars );
	}
	
	// point of love already shared
	// love points
//		random points for tests
//		for(i = 0; i < 500; i++){
//			lovePointCoords.push([2 * (Math.random() - 0.5) * 90, Math.random() * 360]);
//		}
		
	lovePointsMaterial = new THREE.ParticleBasicMaterial( { map: THREE.ImageUtils.loadTexture( 'textures/ball.png' ), size: 2000, transparent: true } );
	lovePointsGeometry = new THREE.Geometry();
	
	for ( i = 0; i < lovePointCoords.length; i ++ ) {
		vertex = coordsToVector3({latitude: lovePointCoords[i][0], longitude: lovePointCoords[i][1]});
		vertex.multiplyScalar(1.05);
		lovePointsGeometry.vertices.push( vertex );
	}
	
	loveParticleSystem = new THREE.ParticleSystem( lovePointsGeometry, lovePointsMaterial );
	loveParticleSystem.matrixAutoUpdate = false;
	loveParticleSystem.updateMatrix();
	loveParticleSystem.sortParticles = true;
	
	scene.add(loveParticleSystem);

	// star!
	starTexture = THREE.ImageUtils.loadTexture( "textures/star.jpg" );
	materialStar = new THREE.MeshBasicMaterial( { color: 0xffffff, map: starTexture } );

	meshStar = new THREE.Mesh( geometry, materialStar );
	meshStar.position.set( starRadius, 0, 0 );
	meshStar.scale.set( starScale, starScale, starScale );
	scene.add( meshStar );
	
	// postprocess
	composer = new THREE.EffectComposer( renderer);
	
	renderModel = new THREE.RenderPass( scene, camera );
	effectBloom = new THREE.BloomPass( 0 ); // effectBloom.screenUniforms[ "opacity" ].value = 0
	
	effectScreen = new THREE.ShaderPass( THREE.ShaderExtras.screen );
	effectScreen.renderToScreen = true;
	
	composer.addPass(renderModel);
	composer.addPass(effectBloom);
	composer.addPass(effectScreen);

	stats = new Stats();
	stats.domElement.style.position = 'absolute';
	stats.domElement.style.top = '0px';
	stats.domElement.style.zIndex = 100;
	container.appendChild( stats.domElement );

	window.addEventListener( 'resize', onWindowResize, false );
}

function success(position) {
	myCoords = position.coords;
	addLovePoint(myCoords);
	
	setTimeout(startAnim, 1500);
}

function error() {
	myCoords = {latitude: (Math.random() - 0.5) * 180, longitude: Math.random() * 360, random: true}; // some random coords
	
	setTimeout(startAnim, 1500);
}

window.onload = function() {
	if ( !Detector.webgl ) {
		Detector.addGetWebGLMessage();
		return;
	}
	
	boxDiv = $('#messageBox')[0];
	
	$('#shareSwitcher').click(function(){
		$('#messageBox').fadeOut(1000);
		$('#shareBox').fadeIn(1000);
	});
	
	$('#submitShare').click(function(){
		if($('#messageArea').val().length > 0){
			addLoveMessage(myCoords, $('#messageArea').val());
		}
	});

	init();
	animate();
	
	if (navigator.geolocation) {
		navigator.geolocation.getCurrentPosition(success, error);
	} else {
		error();
	}
};

}());