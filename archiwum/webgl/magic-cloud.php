<html><head>
<title>magic cloud</title>
<meta http-equiv="content-type" content="text/html; utf-8">
<script type="text/javascript" src="http://www.google-analytics.com/ga.js"></script>
<script type="text/javascript">
	//<![CDATA[
	var pageTracker = _gat._getTracker("UA-441633-3");
	pageTracker._initData();
	pageTracker._trackPageview();
	//]]>
</script>
<script type="text/javascript" src="webgl-utils.js"></script>

<script id="shader-fs" type="x-shader/x-fragment">
#ifdef GL_ES
precision highp float;
#endif

uniform float time;
varying vec2 position;

uniform sampler2D tex0;
uniform sampler2D tex1;

// function 
// _
//  \___
float s(float x){
	return clamp(-x + 2., 0., 1.);
}


float mtime;
void main(void) {
	mtime = 2. * time;
	vec2 pos = (position + 1.) * .5;

	float alpha_cloud = texture2D(tex1, pos).x;
	gl_FragColor = vec4(texture2D(tex0, pos).xyz, s(mtime + alpha_cloud));
}
</script>

<script id="shader-vs" type="x-shader/x-vertex">
attribute vec2 pos;
varying vec2 position;

void main(void) {
	gl_Position = vec4(pos.x, pos.y, 0, 1.0);
	position = pos;
}
</script>


<script type="text/javascript">
function handleLoadedTexture(texture) {
	gl.bindTexture(gl.TEXTURE_2D, texture);
	gl.pixelStorei(gl.UNPACK_FLIP_Y_WEBGL, true);
	gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, texture.image);
	
	gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
	gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR);
	
	// npot (non-power-of-two) textures need this to work
	gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE);
	gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);

	
	gl.bindTexture(gl.TEXTURE_2D, null);
}

function loadTexture(src) {
	var texture = gl.createTexture();
	texture.image = new Image();
	texture.image.onload = function() {
		handleLoadedTexture(texture);
		drawScene();
	}
	texture.image.src = src;
	return texture;
}

var shaderProgram;
function initShaders() {
	var fragmentShader = getShader(gl, "shader-fs");
	var vertexShader = getShader(gl, "shader-vs");

	shaderProgram = gl.createProgram();
	gl.attachShader(shaderProgram, vertexShader);
	gl.attachShader(shaderProgram, fragmentShader);
	gl.linkProgram(shaderProgram);

	if (!gl.getProgramParameter(shaderProgram, gl.LINK_STATUS)) {
		alert("Could not initialise shaders");
	}

	gl.useProgram(shaderProgram);

	shaderProgram.vertexPositionAttribute = gl.getAttribLocation(shaderProgram, "pos");
	gl.enableVertexAttribArray(shaderProgram.vertexPositionAttribute);
}


var cubeVertexPositionBuffer;
function initBuffers() {
	cubeVertexPositionBuffer = gl.createBuffer();
	gl.bindBuffer(gl.ARRAY_BUFFER, cubeVertexPositionBuffer);
	var vertices = [-1, -1, -1, 1, 1, -1,  1, -1, -1, 1, 1, 1];
	gl.bufferData(gl.ARRAY_BUFFER, new Float32Array(vertices), gl.STATIC_DRAW);
	cubeVertexPositionBuffer.itemSize = 2;
	cubeVertexPositionBuffer.numItems = 6;
}


function drawScene() {
	gl.clear(gl.COLOR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT);

	// set vertex positions
	gl.bindBuffer(gl.ARRAY_BUFFER, cubeVertexPositionBuffer);
	gl.vertexAttribPointer(shaderProgram.vertexPositionAttribute, cubeVertexPositionBuffer.itemSize, gl.FLOAT, false, 0, 0);

	// set time
	gl.uniform1f(gl.getUniformLocation(shaderProgram, 'time'), time);

	// set texture
	gl.uniform1i(gl.getUniformLocation(shaderProgram, 'tex0'), 0);
	gl.activeTexture(gl.TEXTURE0);
	gl.bindTexture(gl.TEXTURE_2D, textures[0]);

	// set cloud texture
	gl.uniform1i(gl.getUniformLocation(shaderProgram, 'tex1'), 1);
	gl.activeTexture(gl.TEXTURE1);
	gl.bindTexture(gl.TEXTURE_2D, textures[1]);

	// draw triangles
	gl.drawArrays(gl.TRIANGLES, 0, cubeVertexPositionBuffer.numItems);
}

var gl;
var textures = [];
function webGLStart() {
	var canvas = document.getElementById('canvas');
	gl = WebGLUtils.setupWebGL(canvas);
	initShaders()
	initBuffers();
	
	textures[0] = loadTexture('droga.jpg');
	textures[1] = loadTexture('clouds/cloud.jpg');

	gl.clearColor(0.0, 0.0, 0.0, 0.0);
	gl.viewport(0, 0, canvas.width, canvas.height);

	gl.blendFunc(gl.SRC_ALPHA, gl.ONE_MINUS_SRC_ALPHA)
	gl.enable(gl.BLEND);
}

var time = 0;
var animating = false;
function loop(){
	time += .01 * dif;
	time = +time.toFixed(2);
	if(time <= 1 && time >= 0){
		drawScene();
		
		
		requestAnimFrame(loop);
		animating = true;		
	}else{
		animating = false;
	}
	
	
}

var dif = -1;
function anim(){
	dif *= -1;
	if(!animating){
		loop();
	}
}

function loadCloud(name){
	gl.deleteTexture(textures[1]);
	textures[1] = loadTexture(name);
}

onload = webGLStart;
</script>
</head>
<body>
	<h1>Magic cloud</h1>
	<p>Same effect as previous one but using fragment shaders</p>
	<p>Click on the image to see the animation</p>
	<p><label for="cloudType">choose cloud type</label>
	<select name="cloudType" id="cloudType" onchange="loadCloud(this.value)">
		<option value="clouds/cloud.jpg">select</option>
		<?php
			foreach(glob('clouds/*.jpg') as $name){
				printf('<option value="%s">%s</option>', $name, basename($name, '.jpg'));
			}
		?>
	</select></p>
	<div style="width: 500px; height: 500px; border: 2px solid" onclick="anim()">
		<canvas id="canvas" style="border: none;" width="500" height="500"></canvas>
	</div>
	<p>This image is taken by <a href="http://www.flickr.com/photos/heypaul/1428677/">Hey Paul</a> and licensed as <a href="http://creativecommons.org/licenses/by/2.0/deed.en">CC BY 2.0</a></p>
</body></html>