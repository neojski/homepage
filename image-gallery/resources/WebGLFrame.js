function WebGLFrame(canvasNode, fragmentShaderStr){
	if(fragmentShaderStr == undefined){
		alert("You've to define fragment shader!");
	}
	
	if(!WebGLFrame.webglAvail){
		alert("No webgl!"); // should check it
	}
				
	var gl = WebGLUtils.setupWebGL(canvasNode);
	var textures = [];
	var uniforms1f = {};
				
	function handleLoadedTexture(glTexture) {
		gl.bindTexture(gl.TEXTURE_2D, glTexture);
	
		gl.pixelStorei(gl.UNPACK_FLIP_Y_WEBGL, true);
		gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, glTexture.image);
	
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR);
	
		// npot (non-power-of-two) textures need this to work
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);

		gl.bindTexture(gl.TEXTURE_2D, null);
	}
	
	var that = this;
	this.loadTexture = function(src) {
		var tex = gl.createTexture();
		tex.image = new Image();
		tex.image.src = src;
		tex.image.onload = function() {
			handleLoadedTexture(tex);
						
			that.render();
		}
		
		textures.push(tex);
		return textures.length - 1;
	}
	this.loadSvgTexture = function(svg){
		var tex = gl.createTexture();
		tex.image = svg;
	
		handleLoadedTexture(tex);
		
		that.render();
					
		textures.push(tex);
		return textures.length - 1;
	}
				
	var shaderProgram;
	function initShaders(fragmentShaderStr) {
		var fragmentShader = getShader(gl, fragmentShaderStr, gl.FRAGMENT_SHADER);
		var vertexShader = getShader(gl, "attribute vec2 pos;\
			varying vec2 position;\
			void main(void) {\
			gl_Position = vec4(pos.x, pos.y, 0, 1.0);\
			position = pos;\
			}", gl.VERTEX_SHADER); // usually the same vertex shader

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
				
	this.setUniform1f = function(name, value){
		uniforms1f[name] = value;
	}
				
	this.render = function() {
		var i, uniform;
		gl.clear(gl.COLOR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT);

		// set vertex positions
		gl.bindBuffer(gl.ARRAY_BUFFER, rectVertexPositionBuffer);
		gl.vertexAttribPointer(shaderProgram.vertexPositionAttribute, rectVertexPositionBuffer.itemSize, gl.FLOAT, false, 0, 0);

		// set uniforms1f
		for(uniform in uniforms1f){
			gl.uniform1f(gl.getUniformLocation(shaderProgram, uniform), uniforms1f[uniform]);
		}

		// set texture
		for(i = 0; i < textures.length; i++){
			gl.uniform1i(gl.getUniformLocation(shaderProgram, 'tex'+i), i);
			gl.activeTexture(gl["TEXTURE"+i]);
			gl.bindTexture(gl.TEXTURE_2D, textures[i]);
		}

		// draw triangles
		gl.drawArrays(gl.TRIANGLES, 0, rectVertexPositionBuffer.numItems);
	}
	
	// load shaders
	initShaders(fragmentShaderStr);
			
	// load rectangle
	var rectVertexPositionBuffer = gl.createBuffer();
	gl.bindBuffer(gl.ARRAY_BUFFER, rectVertexPositionBuffer);
	var vertices = [-1, -1, -1, 1, 1, -1,  1, -1, -1, 1, 1, 1];
	gl.bufferData(gl.ARRAY_BUFFER, new Float32Array(vertices), gl.STATIC_DRAW);
	rectVertexPositionBuffer.itemSize = 2;
	rectVertexPositionBuffer.numItems = 6;

	gl.clearColor(0.0, 0.0, 0.0, 0.0);
	gl.viewport(0, 0, canvasNode.width, canvasNode.height);

	gl.blendFunc(gl.SRC_ALPHA, gl.ONE_MINUS_SRC_ALPHA)
	gl.enable(gl.BLEND);
}
WebGLFrame.timer = function(){
	var startTime = new Date();
	this.get = function(){
		return new Date() - startTime;
	}
}
WebGLFrame.webglAvail = ( function () {
	try {
		return !! window.WebGLRenderingContext && !! document.createElement( 'canvas' ).getContext( 'experimental-webgl' );
	} catch( e ) {
		return false;
	}
} )(); // taken from THREE.js by mrdoob