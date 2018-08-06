#ifdef GL_ES
precision highp float;
#endif

const float PI = 3.1415926535;

uniform float time;
varying vec2 position;

uniform sampler2D tex0;
uniform sampler2D tex1;

float f(float x) {
	float c = .05; // how near is end
	return (1. - c) * pow(cos(PI * x / 2.), 2.) + c;
}

void main(void) {
	vec2 pos = (position + 1.) * .5;

	float sx = 1.;
	float x, y, t;

	if (time < .5) {
		// deform
		t = 2. * time;
		t = t * t;

		x = t * (pos.x - (1. - f(pos.y)) * sx) / (f(pos.y) * sx + f(pos.y) * (1. - sx)) + (1. - t) * pos.x;
		y = pos.y;
	} else {
		// up!
		t = 2. * time - 1.;
		x = (pos.x - (1. - f(pos.y)) * sx) / (f(pos.y) * sx + f(pos.y) * (1. - sx));
		y = pos.y - t;
	}


	float alpha = 1.0;
	if (x > 1.0 || x < 0.0 || y > 1.0 || y < 0.0) {
		alpha = 0.0;
	}

	gl_FragColor = texture2D(tex1, vec2(x, y)) * alpha;
}