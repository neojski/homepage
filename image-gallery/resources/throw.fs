#ifdef GL_ES
precision highp float;
#endif

const float PI = 3.1415926535;

uniform float time;
varying vec2 position;

uniform sampler2D tex0;
uniform float vecX;
uniform float vecY;
uniform float margin;

void main(void) {
	vec2 pos = (1. + margin) * position;

	float x = pos.x;
	float y = pos.y;

	vec2 from = vec2(pos.x, pos.y);
	vec2 to;

	vec2 force = vec2(vecX, vecY) * sin(PI * 5. * (1. - time));

    float m = 1.3;
	vec2 v[4];
	v[0] = vec2(-m, -m);
	v[1] = vec2(m, -m);
	v[2] = vec2(m, m);
	v[3] = vec2(-m, m);

	float minDist = 2.;
	for (int i = 0; i < 4; i++) {
		minDist = min(minDist, distance(v[i], pos));
	}

	float intensity = pow(minDist - m, 2.) - m;

	to = from + intensity * force;

	// scale texture
	vec2 texture = .5 * (mix(from, to, (1. - time) * (1. - time)) + vec2(1., 1.));

	float alpha = 1.0;
	if (texture.x > 1.0 || texture.x < 0.0 || texture.y > 1.0 || texture.y < 0.0) {
		alpha = 0.0;
	}

	gl_FragColor = texture2D(tex0, texture) * alpha;
}