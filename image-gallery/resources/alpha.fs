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