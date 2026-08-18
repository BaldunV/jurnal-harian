@extends('layouts.app')

@section('title', 'Mascot Bintang - Sanctuary & Stage')

@section('content')
    <!-- MASCOT SANCTUARY & STAGE -->
    <div class="mascot-stage relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-950 via-emerald-900 to-teal-900 p-6 text-white shadow-2xl sm:p-8">
        <!-- Background shader WebGL (mesh drift) -->
        <canvas
            id="mascot-bg-webgl"
            class="pointer-events-none absolute inset-0 z-0 block h-full w-full"
            aria-hidden="true"
        ></canvas>
        <!-- Bintang dekoratif -->
        <div
            class="pointer-events-none absolute inset-0 z-[1]"
            aria-hidden="true"
        >
            @for ($i = 0; $i < 18; $i++)
                <span
                    class="mascot-star absolute rounded-full bg-white"
                    style="
                        width: {{ rand(3, 8) }}px;
                        height: {{ rand(3, 8) }}px;
                        top: {{ rand(0, 90) }}%;
                        left: {{ rand(0, 95) }}%;
                        opacity: {{ rand(20, 70) / 100 }};
                        animation-delay: {{ rand(0, 400) / 100 }}s;
                    "
                ></span>
            @endfor
        </div>

        <!-- Stage Maskot -->
        <div class="relative z-10 mx-auto flex w-full max-w-6xl flex-col items-center justify-center gap-4 sm:flex-row sm:gap-6">
            <button
                type="button"
                onclick="switchMascotNav(-1)"
                class="mascot-nav-btn order-2 flex h-12 w-12 shrink-0 items-center justify-center rounded-full border border-white/30 bg-white/15 shadow-xl backdrop-blur transition-all hover:bg-white/30 active:scale-90 sm:order-1 sm:h-14 sm:w-14"
                aria-label="Maskot sebelumnya"
            >
                @include('partials.icon', [
                    'name' => 'chevron-left',
                    'class' => 'h-6 w-6 text-white',
                ])
            </button>

            <div class="relative order-1 flex w-full min-w-0 flex-1 items-center justify-center sm:order-2">
                <canvas
                    id="mascot-stage-rive"
                    class="block aspect-square max-h-[72dvh] w-full max-w-[680px] select-none rounded-3xl border-2 border-white/40 bg-white/5 shadow-[0_0_30px_rgba(255,255,255,0.15),inset_0_0_30px_rgba(0,0,0,0.25)] backdrop-blur-sm sm:aspect-[4/3] sm:max-w-[560px]"
                    style="cursor: pointer; touch-action: none; -webkit-tap-highlight-color: transparent;"
                    aria-label="Animasi maskot Bintang di panggung. Ganti maskot lewat tombol panah."
                ></canvas>
            </div>

            <button
                type="button"
                onclick="switchMascotNav(1)"
                class="mascot-nav-btn order-3 flex h-12 w-12 shrink-0 items-center justify-center rounded-full border border-white/30 bg-white/15 shadow-xl backdrop-blur transition-all hover:bg-white/30 active:scale-90 sm:h-14 sm:w-14"
                aria-label="Maskot berikutnya"
            >
                @include('partials.icon', [
                    'name' => 'chevron-right',
                    'class' => 'h-6 w-6 text-white',
                ])
            </button>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/rive.min.js') }}"></script>

    <script>
        (() => {
            'use strict';

            if (typeof rive === 'undefined') {
                console.error('Rive library gagal dimuat.');
                return;
            }

            if (rive.RuntimeLoader) {
                rive.RuntimeLoader.setWasmUrl(
                    @json(asset('js/rive.wasm'))
                );
            }

            const canvas = document.getElementById('mascot-stage-rive');

            if (!canvas) {
                console.error('Canvas maskot tidak ditemukan.');
                return;
            }

            const mascots = [
                @json(asset('rive/shake-it-duo.riv')),
                @json(asset('rive/hard-choice.riv')),
            ];

            const mascotNames = [
                'Goyang 2',
                'Pilihan Sulit',
            ];

            let instance = null;
            let currentIndex = 0;
            let triggerCycle = null;
            let triggerTimer = null;
            let triggerIndex = 0;

            function updateMascotBadge() {
                const badge = document.getElementById('mascot-name-badge');

                if (badge) {
                    badge.textContent = mascotNames[currentIndex];
                }
            }

            function resizeCanvas() {
                if (
                    instance &&
                    canvas.offsetWidth > 0 &&
                    canvas.offsetHeight > 0 &&
                    typeof instance.resizeDrawingSurfaceToCanvas === 'function'
                ) {
                    instance.resizeDrawingSurfaceToCanvas();
                }
            }

            const canvasResizeObserver = new ResizeObserver(() => {
                resizeCanvas();
            });
            canvasResizeObserver.observe(canvas);

            function stopTriggerLoop() {
                if (triggerTimer !== null) {
                    clearTimeout(triggerTimer);
                    clearInterval(triggerTimer);
                    triggerTimer = null;
                }
                triggerCycle = null;
            }

            function startTriggerLoop() {
                stopTriggerLoop();

                const check = () => {
                    const sm = instance?.animator?.stateMachines?.[0];

                    if (!sm) {
                        return 'retry';
                    }

                    const inputs = sm?.inputs ?? [];

                    if (!inputs.length) {
                        return 'done';
                    }

                    triggerCycle = inputs.filter(
                        (input) => typeof input.fire === 'function'
                    );

                    return triggerCycle.length > 0 ? 'start' : 'done';
                };

                const status = check();

                if (status === 'retry') {
                    if (triggerTimer === null) {
                        triggerTimer = setTimeout(() => {
                            triggerTimer = null;
                            startTriggerLoop();
                        }, 1000);
                    }
                    return;
                }

                if (status !== 'start') {
                    return;
                }

                if (triggerTimer !== null) {
                    clearTimeout(triggerTimer);
                    triggerTimer = null;
                }

                const fire = () => {
                    if (!triggerCycle || !triggerCycle.length) {
                        return;
                    }

                    triggerCycle[triggerIndex % triggerCycle.length].fire();
                    triggerIndex++;
                };

                fire();
                triggerTimer = setInterval(fire, 3000);
            }

            function loadRive(source, artboardName, stateMachineName) {
                try {
                    if (instance) {
                        if (typeof instance.stop === 'function') {
                            instance.stop();
                        }
                        if (typeof instance.cleanup === 'function') {
                            instance.cleanup();
                        }
                        instance = null;
                    }

                    stopTriggerLoop();

                    const options = {
                        src: source,
                        canvas,
                        autoplay: true,
                        layout: new rive.Layout({
                            fit: rive.Fit.Contain,
                            alignment: rive.Alignment.Center,
                        }),
                        onLoad: () => {
                            resizeCanvas();

                            const currentName = instance.artboard?.name;
                            const contents = instance.contents;
                            const artboards = contents?.artboards ?? [];
                            const target = artboards.find(
                                (artboard) => artboard.stateMachines.length > 0
                            );

                            if (target && target.name !== currentName) {
                                const smName = target.stateMachines[0].name;
                                loadRive(source, target.name, smName);
                                return;
                            }

                            if (
                                target &&
                                typeof instance.play === 'function'
                            ) {
                                instance.play(
                                    stateMachineName ||
                                        target.stateMachines[0].name
                                );
                            }

                            if (typeof instance.setupRiveListeners === 'function') {
                                instance.setupRiveListeners({
                                    isTouchScrollEnabled: true,
                                });
                            }
                            console.log('Maskot aktif:', mascotNames[currentIndex]);

                            setTimeout(() => {
                                if (instance && instance.loaded) {
                                    startTriggerLoop();
                                }
                            }, 500);
                        },
                        onLoadError: (error) => {
                            console.error('Rive gagal:', error);
                        },
                    };

                    if (artboardName && stateMachineName) {
                        options.artboard = artboardName;
                        options.stateMachines = [stateMachineName];
                    }

                    instance = new rive.Rive(options);
                } catch (error) {
                    console.error('Error saat menjalankan Rive:', error);
                }
            }

            function switchMascot(step) {
                currentIndex =
                    (currentIndex + step + mascots.length) % mascots.length;

                updateMascotBadge();
                loadRive(mascots[currentIndex]);
            }

            window.switchMascotNav = switchMascot;

            loadRive(mascots[currentIndex]);

            canvas.addEventListener('click', () => {
                if (instance && typeof instance.play === 'function' && instance.isPaused) {
                    instance.play();
                }

                if (triggerCycle && triggerCycle.length) {
                    triggerCycle[triggerIndex % triggerCycle.length].fire();
                    triggerIndex++;
                }
            });

            canvas.addEventListener('contextmenu', (event) => {
                event.preventDefault();
            });

            window.addEventListener('resize', resizeCanvas);

            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (!instance) {
                            return;
                        }

                        if (entry.isIntersecting) {
                            instance.play();
                        } else {
                            instance.pause();
                        }
                    });
                });

                observer.observe(canvas);
            }
        })();
    </script>

    <script>
        (() => {
            'use strict';

            const bgCanvas = document.getElementById('mascot-bg-webgl');

            if (!bgCanvas) {
                return;
            }

            const gl = bgCanvas.getContext('webgl', {
                alpha: false,
                antialias: false,
                depth: false,
                stencil: false,
            });

            if (!gl) {
                console.error('WebGL tidak didukung.');
                return;
            }

            const VERTEX_SHADER = [
                'attribute vec2 a_pos;',
                'void main() {',
                '    gl_Position = vec4(a_pos, 0.0, 1.0);',
                '}',
            ].join('\n');

            const FRAGMENT_SHADER = [
                '// "Mesh drift" — made with the 21st.dev Shader Builder',
                '// Packed WebGL1 uniforms (the shader exposes readable u_* aliases as macros):',
                '//   u_colors[8] (first 4 used)',
                '//   vec3(0.008, 0.043, 0.033)',
                '//   vec3(0.050, 0.300, 0.240)',
                '//   vec3(0.240, 0.500, 0.400)',
                '//   vec3(0.550, 0.720, 0.620)',
                '//   u_scene = vec4(canvas width, canvas height, seconds * 0.73, 4.0)',
                '//   u_shape = vec4(1.16, 0.34, 0.50, 0.00)',
                '//   u_surface = vec4(2.40, 1.16, 0.00, 1.00)',
                '//   u_finish = vec4(0.00, 0.00, 0.000, 0.09)',
                '//   u_transform = vec4(1453.0, 0.00, 0.00, 0.0)',
                '//   u_space = vec4(0.00, 0.00, pointer x, pointer y)',
                '//   u_cursor = vec4(presence, 2.0, 0.65, 0.46)',
                '',
                '#ifdef GL_FRAGMENT_PRECISION_HIGH',
                'precision highp float;',
                '#else',
                'precision mediump float;',
                '#endif',
                '',
                'uniform vec3 u_colors[8];',
                '// Seven packed vectors + eight colour vectors = 15 fragment uniform vectors,',
                '// one below WebGL1\'s guaranteed minimum. Macros preserve the public u_* API.',
                'uniform vec4 u_scene;      // resolution.xy, time, colour count',
                'uniform vec4 u_shape;      // scale, intensity, paramA, warp',
                'uniform vec4 u_surface;    // detail, contrast, brightness, saturation',
                'uniform vec4 u_finish;     // hue, vignette, blur, grain',
                'uniform vec4 u_transform;  // seed, rotation, drift, OKLab toggle',
                'uniform vec4 u_space;      // offset.xy, pointer.xy',
                'uniform vec4 u_cursor;',
                '',
                '#define u_resolution u_scene.xy',
                '#define u_time u_scene.z',
                '#define u_colorCount u_scene.w',
                '#define u_scale u_shape.x',
                '#define u_intensity u_shape.y',
                '#define u_paramA u_shape.z',
                '#define u_warp u_shape.w',
                '#define u_detail u_surface.x',
                '#define u_contrast u_surface.y',
                '#define u_brightness u_surface.z',
                '#define u_saturation u_surface.w',
                '#define u_hue u_finish.x',
                '#define u_vignette u_finish.y',
                '#define u_blur u_finish.z',
                '#define u_grain u_finish.w',
                '#ifdef GL_FRAGMENT_PRECISION_HIGH',
                '#define u_seed u_transform.x',
                '#else',
                '// Keep hash inputs inside mediump\'s guaranteed ±2^14 range.',
                '#define u_seed mod(u_transform.x, 31.0)',
                '#endif',
                '#define u_rotate u_transform.y',
                '#define u_drift u_transform.z',
                '#define u_oklab u_transform.w',
                '#define u_offset u_space.xy',
                '#define u_mouse u_space.zw',
                '#define u_cursorPresence u_cursor.x',
                '#define u_cursorEffect u_cursor.y',
                '#define u_cursorStrength u_cursor.z',
                '#define u_cursorRadius u_cursor.w',
                '',
                'float hash21(vec2 p) {',
                '#ifndef GL_FRAGMENT_PRECISION_HIGH',
                '  p = mod(p, 31.0);',
                '#endif',
                '  p = fract(p * vec2(234.34, 435.345));',
                '  p += dot(p, p + 34.23);',
                '  return fract(p.x * p.y);',
                '}',
                '',
                '// Even, un-structured white noise for film grain (Dave Hoskins hash12). The',
                '// multiply hash above is fine for value noise but shows a faint axis-aligned',
                '// mesh at integer fragment coords, which reads as a net over flat areas.',
                'float grainHash(vec2 p) {',
                '  vec3 p3 = fract(vec3(p.xyx) * 0.1031);',
                '  p3 += dot(p3, p3.yzx + 33.33);',
                '  return fract((p3.x + p3.y) * p3.z);',
                '}',
                '',
                'vec2 hash22(vec2 p) {',
                '#ifndef GL_FRAGMENT_PRECISION_HIGH',
                '  p = mod(p, 31.0);',
                '#endif',
                '  float n = sin(dot(p, vec2(41.0, 289.0)));',
                '  return fract(vec2(15731.743, 7892.321) * n);',
                '}',
                '',
                'float noise(vec2 p) {',
                '  vec2 i = floor(p);',
                '  vec2 f = fract(p);',
                '  vec2 u = f * f * (3.0 - 2.0 * f);',
                '  return mix(',
                '    mix(hash21(i), hash21(i + vec2(1.0, 0.0)), u.x),',
                '    mix(hash21(i + vec2(0.0, 1.0)), hash21(i + vec2(1.0, 1.0)), u.x),',
                '    u.y);',
                '}',
                '',
                'float fbm(vec2 p) {',
                '  float v = 0.0;',
                '  float a = 0.5;',
                '  for (int i = 0; i < 5; i++) {',
                '    v += a * noise(p);',
                '    p = p * 2.03 + vec2(17.0, 9.2);',
                '    a *= 0.5;',
                '  }',
                '  return v;',
                '}',
                '',
                '// --- OKLab colour mixing (perceptual), gated by u_oklab ---',
                'vec3 srgbToLinear(vec3 c) {',
                '  return mix(c / 12.92, pow((c + 0.055) / 1.055, vec3(2.4)),',
                '    step(0.04045, c));',
                '}',
                'vec3 linearToSrgb(vec3 c) {',
                '  // max() guards the sRGB branch: out-of-gamut OKLab interpolations can send a',
                '  // channel negative, and pow(negative, …) is NaN which mix()/step() would',
                '  // then propagate. The linear branch clips such channels to 0 downstream.',
                '  return mix(c * 12.92, 1.055 * pow(max(c, vec3(0.0)), vec3(1.0 / 2.4)) - 0.055,',
                '    step(0.0031308, c));',
                '}',
                'vec3 linToOklab(vec3 c) {',
                '  float l = 0.4122214708 * c.r + 0.5363325363 * c.g + 0.0514459929 * c.b;',
                '  float m = 0.2119034982 * c.r + 0.6806995451 * c.g + 0.1073969566 * c.b;',
                '  float s = 0.0883024619 * c.r + 0.2817188376 * c.g + 0.6299787005 * c.b;',
                '  l = pow(max(l, 0.0), 1.0 / 3.0);',
                '  m = pow(max(m, 0.0), 1.0 / 3.0);',
                '  s = pow(max(s, 0.0), 1.0 / 3.0);',
                '  return vec3(',
                '    0.2104542553 * l + 0.7936177850 * m - 0.0040720468 * s,',
                '    1.9779984951 * l - 2.4285922050 * m + 0.4505937099 * s,',
                '    0.0259040371 * l + 0.7827717662 * m - 0.8086757660 * s);',
                '}',
                'vec3 oklabToLin(vec3 c) {',
                '  float l = c.x + 0.3963377774 * c.y + 0.2158037573 * c.z;',
                '  float m = c.x - 0.1055613458 * c.y - 0.0638541728 * c.z;',
                '  float s = c.x - 0.0894841775 * c.y - 1.2914855480 * c.z;',
                '  l = l * l * l; m = m * m * m; s = s * s * s;',
                '  return vec3(',
                '    4.0767416621 * l - 3.3077115913 * m + 0.2309699292 * s,',
                '    -1.2684380046 * l + 2.6097574011 * m - 0.3413193965 * s,',
                '    -0.0041960863 * l - 0.7034186147 * m + 1.7076147010 * s);',
                '}',
                'vec3 mixColour(vec3 a, vec3 b, float t) {',
                '  if (u_oklab > 0.5) {',
                '    vec3 la = linToOklab(srgbToLinear(a));',
                '    vec3 lb = linToOklab(srgbToLinear(b));',
                '    return clamp(linearToSrgb(oklabToLin(mix(la, lb, t))), 0.0, 1.0);',
                '  }',
                '  return mix(a, b, t);',
                '}',
                '',
                '// Mix through the recipe colours; x is clamped to 0..1. WebGL1 forbids',
                '// dynamic uniform indexing in fragment shaders, hence the constant loop.',
                'vec3 palette(float x) {',
                '  float n = max(u_colorCount - 1.0, 1.0);',
                '  float f = clamp(x, 0.0, 1.0) * n;',
                '  vec3 col = u_colors[0];',
                '  for (int i = 0; i < 7; i++) {',
                '    if (float(i) < n)',
                '      col = mixColour(col, u_colors[i + 1],',
                '        smoothstep(0.0, 1.0, clamp(f - float(i), 0.0, 1.0)));',
                '  }',
                '  return col;',
                '}',
                '',
                'vec3 hueRotate(vec3 col, float a) {',
                '  const mat3 toYIQ = mat3(0.299, 0.596, 0.211,',
                '                          0.587, -0.274, -0.523,',
                '                          0.114, -0.322, 0.312);',
                '  const mat3 toRGB = mat3(1.0, 1.0, 1.0,',
                '                          0.956, -0.272, -1.106,',
                '                          0.621, -0.647, 1.703);',
                '  vec3 yiq = toYIQ * col;',
                '  float ca = cos(a), sa = sin(a);',
                '  yiq = vec3(yiq.x, yiq.y * ca - yiq.z * sa, yiq.y * sa + yiq.z * ca);',
                '  return toRGB * yiq;',
                '}',
                '',
                'vec3 shade(vec2 uv, vec2 p, float t) {',
                '  vec3 acc = u_colors[0] * 0.15;',
                '  float total = 0.15;',
                '  for (int i = 0; i < 8; i++) {',
                '    if (float(i) >= u_colorCount) break;',
                '    float fi = float(i);',
                '    vec2 c = vec2(',
                '      sin(t * (0.21 + fi * 0.071) + fi * 2.4 + u_seed),',
                '      cos(t * (0.17 + fi * 0.093) + fi * 1.7)) * (0.45 + u_intensity * 0.35);',
                '    float w = exp(-dot(p - c, p - c) * 6.0);',
                '    acc += u_colors[i] * w;',
                '    total += w;',
                '  }',
                '  return acc / total;',
                '}',
                '',
                'void main() {',
                '  vec2 uv = gl_FragCoord.xy / u_resolution.xy;',
                '  vec2 screenUv = uv;',
                '  vec2 p = (gl_FragCoord.xy - 0.5 * u_resolution.xy)',
                '    / min(u_resolution.x, u_resolution.y);',
                '  float cursorMask = 0.0;',
                '',
                '  // Cursor modes 1–3 are local distortions. Push shifts the same screen-space',
                '  // coordinates before field transforms, so Zoom/Rotate don\'t change its feel.',
                '  if (u_cursorPresence > 0.001) {',
                '    // u_mouse is normalized to -1..1 in canvas space. Convert it to the same',
                '    // aspect-corrected screen space as p so effects stay under the cursor.',
                '    vec2 cursor = (0.5 * u_mouse * u_resolution.xy)',
                '      / min(u_resolution.x, u_resolution.y);',
                '    vec2 cursorDelta = p - cursor;',
                '    if (u_cursorEffect < 0.5) {',
                '      p += cursor * u_cursorPresence * u_cursorStrength * 0.55;',
                '    } else {',
                '      float cursorDistance = length(cursorDelta);',
                '      vec2 cursorDirection = cursorDelta / max(cursorDistance, 0.0001);',
                '      cursorMask = u_cursorPresence',
                '        * (1.0 - smoothstep(0.0, u_cursorRadius, cursorDistance));',
                '      if (u_cursorEffect < 1.5) {',
                '        p -= cursorDirection * cursorMask * u_cursorStrength * 0.24;',
                '      } else if (u_cursorEffect < 2.5) {',
                '        float cursorAngle = cursorMask * u_cursorStrength * 2.2;',
                '        float cc = cos(cursorAngle), cs = sin(cursorAngle);',
                '        p = cursor + mat2(cc, -cs, cs, cc) * cursorDelta;',
                '      } else if (u_cursorEffect < 3.5) {',
                '        float ripple = sin(',
                '          cursorDistance / max(u_cursorRadius, 0.001) * 18.0 - u_time * 5.0);',
                '        p -= cursorDirection * ripple * cursorMask * u_cursorStrength * 0.07;',
                '      }',
                '    }',
                '  }',
                '',
                '  // Keep presets that read uv (rather than p) in the same warped space.',
                '  uv = p * min(u_resolution.x, u_resolution.y) / u_resolution.xy + 0.5;',
                '  p *= u_scale;',
                '  // Field transform: rotate, pan, pointer push, slow drift.',
                '  if (abs(u_rotate) > 0.0001) {',
                '    float cr = cos(u_rotate), sr = sin(u_rotate);',
                '    p = mat2(cr, -sr, sr, cr) * p;',
                '  }',
                '  p += u_offset;',
                '  if (u_drift > 0.0001)',
                '    p += u_drift * vec2(sin(u_time * 0.31), cos(u_time * 0.23));',
                '  // Organic domain warp.',
                '  if (u_warp > 0.0) {',
                '    p += u_warp * (vec2(',
                '      fbm(p * u_detail + u_seed),',
                '      fbm(p * u_detail + vec2(5.2, 1.3))) - 0.5);',
                '  }',
                '  // Shade, with an optional soft 5-tap blur.',
                '  vec3 col;',
                '  if (u_blur > 0.0) {',
                '    float e = u_blur;',
                '    float pe = e * u_scale;',
                '    vec2 uvE = vec2(e) * min(u_resolution.x, u_resolution.y) / u_resolution.xy;',
                '    col  = shade(uv, p, u_time) * 0.36;',
                '    col += shade(uv + vec2(uvE.x, 0.0), p + vec2(pe, 0.0), u_time) * 0.16;',
                '    col += shade(uv - vec2(uvE.x, 0.0), p - vec2(pe, 0.0), u_time) * 0.16;',
                '    col += shade(uv + vec2(0.0, uvE.y), p + vec2(0.0, pe), u_time) * 0.16;',
                '    col += shade(uv - vec2(0.0, uvE.y), p - vec2(0.0, pe), u_time) * 0.16;',
                '  } else {',
                '    col = shade(uv, p, u_time);',
                '  }',
                '  // Post: contrast, saturation, hue, brightness, vignette, grain.',
                '  if (abs(u_contrast - 1.0) > 0.0001)',
                '    col = (col - 0.5) * u_contrast + 0.5;',
                '  if (abs(u_saturation - 1.0) > 0.0001) {',
                '    float luma = dot(col, vec3(0.299, 0.587, 0.114));',
                '    col = mix(vec3(luma), col, u_saturation);',
                '  }',
                '  if (abs(u_hue) > 0.0001)',
                '    col = hueRotate(col, u_hue);',
                '  if (abs(u_brightness) > 0.0001)',
                '    col += u_brightness;',
                '  if (u_vignette > 0.0001) {',
                '    float vd = length(screenUv - 0.5) * 1.41421356;',
                '    col *= 1.0 - u_vignette * smoothstep(0.35, 1.0, vd);',
                '  }',
                '  if (u_cursorPresence > 0.001 && u_cursorEffect > 3.5)',
                '    col += (vec3(0.18) + col * 0.12) * cursorMask * u_cursorStrength;',
                '  if (u_grain > 0.0001)',
                '    col += (grainHash(',
                '      gl_FragCoord.xy + vec2(u_seed * 17.0, u_seed * 31.0)) - 0.5) * u_grain;',
                '  gl_FragColor = vec4(clamp(col, 0.0, 1.0), 1.0);',
                '}',
            ].join('\n');

            function compileShader(type, source) {
                const shader = gl.createShader(type);
                gl.shaderSource(shader, source);
                gl.compileShader(shader);

                if (!gl.getShaderParameter(shader, gl.COMPILE_STATUS)) {
                    console.error(
                        'Shader gagal dikompilasi:',
                        gl.getShaderInfoLog(shader)
                    );
                    gl.deleteShader(shader);
                    return null;
                }

                return shader;
            }

            const vertexShader = compileShader(gl.VERTEX_SHADER, VERTEX_SHADER);
            const fragmentShader = compileShader(gl.FRAGMENT_SHADER, FRAGMENT_SHADER);

            if (!vertexShader || !fragmentShader) {
                return;
            }

            const program = gl.createProgram();
            gl.attachShader(program, vertexShader);
            gl.attachShader(program, fragmentShader);
            gl.linkProgram(program);

            if (!gl.getProgramParameter(program, gl.LINK_STATUS)) {
                console.error(
                    'Program gagal di-link:',
                    gl.getProgramInfoLog(program)
                );
                return;
            }

            gl.useProgram(program);

            const buffer = gl.createBuffer();
            gl.bindBuffer(gl.ARRAY_BUFFER, buffer);
            gl.bufferData(
                gl.ARRAY_BUFFER,
                new Float32Array([-1, -1, 3, -1, -1, 3]),
                gl.STATIC_DRAW
            );

            const position = gl.getAttribLocation(program, 'a_pos');
            gl.enableVertexAttribArray(position);
            gl.vertexAttribPointer(position, 2, gl.FLOAT, false, 0, 0);

            const uColors = gl.getUniformLocation(program, 'u_colors');
            const uScene = gl.getUniformLocation(program, 'u_scene');
            const uShape = gl.getUniformLocation(program, 'u_shape');
            const uSurface = gl.getUniformLocation(program, 'u_surface');
            const uFinish = gl.getUniformLocation(program, 'u_finish');
            const uTransform = gl.getUniformLocation(program, 'u_transform');
            const uSpace = gl.getUniformLocation(program, 'u_space');
            const uCursor = gl.getUniformLocation(program, 'u_cursor');

            gl.uniform3fv(uColors, new Float32Array([
                0.008, 0.043, 0.033,
                0.05, 0.30, 0.24,
                0.24, 0.50, 0.40,
                0.55, 0.72, 0.62,
                0.0, 0.0, 0.0,
                0.0, 0.0, 0.0,
                0.0, 0.0, 0.0,
                0.0, 0.0, 0.0,
            ]));
            gl.uniform4f(uShape, 1.16, 0.34, 0.5, 0.0);
            gl.uniform4f(uSurface, 2.4, 1.16, 0.0, 1.0);
            gl.uniform4f(uFinish, 0.0, 0.0, 0.0, 0.09);
            gl.uniform4f(uTransform, 1453.0, 0.0, 0.0, 0.0);
            gl.uniform4f(uSpace, 0.0, 0.0, 0.0, 0.0);
            gl.uniform4f(uCursor, 0.0, 2.0, 0.65, 0.46);

            let running = false;
            let frameId = null;
            let startedAt = performance.now();

            function resize() {
                const dpr = Math.min(window.devicePixelRatio || 1, 2);
                const width = Math.max(1, Math.round(bgCanvas.clientWidth * dpr));
                const height = Math.max(1, Math.round(bgCanvas.clientHeight * dpr));

                if (bgCanvas.width !== width || bgCanvas.height !== height) {
                    bgCanvas.width = width;
                    bgCanvas.height = height;
                    gl.viewport(0, 0, width, height);
                }
            }

            function render(now) {
                if (!running) {
                    return;
                }

                const seconds = (now - startedAt) / 1000;

                gl.uniform4f(
                    uScene,
                    bgCanvas.width,
                    bgCanvas.height,
                    seconds * 0.73,
                    4.0
                );
                gl.drawArrays(gl.TRIANGLES, 0, 3);

                frameId = requestAnimationFrame(render);
            }

            function start() {
                if (running) {
                    return;
                }

                running = true;
                startedAt = performance.now();
                frameId = requestAnimationFrame(render);
            }

            function stop() {
                running = false;

                if (frameId !== null) {
                    cancelAnimationFrame(frameId);
                    frameId = null;
                }
            }

            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    stop();
                } else {
                    resize();
                    start();
                }
            });

            window.addEventListener('resize', resize);

            resize();
            start();
        })();
    </script>
@endpush