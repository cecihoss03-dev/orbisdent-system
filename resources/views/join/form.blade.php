<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Unirme a {{ $tenant->business_name }}</title><style>body{font-family:system-ui,sans-serif;background:#f3f4f6;margin:0;display:flex;min-height:100vh;align-items:center;justify-content:center}.c{background:#fff;padding:2rem;border-radius:12px;width:100%;max-width:420px;box-shadow:0 2px 12px #0001}label{display:block;font-size:.85rem;margin:.9rem 0 .25rem}input{width:100%;box-sizing:border-box;padding:.6rem;border:1px solid #d1d5db;border-radius:8px}button{margin-top:1.2rem;width:100%;padding:.7rem;border:0;border-radius:8px;background:#0d9488;color:#fff;font-weight:600;cursor:pointer}.e{color:#b91c1c;font-size:.8rem}.m{color:#6b7280;font-size:.85rem}</style></head><body><div class="c">
<h2 style="margin:0">{{ $tenant->business_name }}</h2>
<p class="m">Crea tu cuenta para unirte al equipo. El administrador de la clínica deberá aprobarte y asignarte un rol antes de que puedas entrar.</p>
<form method="POST" action="{{ route('staff.join.store', $code) }}">@csrf
<label>Nombre completo</label><input name="name" value="{{ old('name') }}" required>@error('name')<div class="e">{{ $message }}</div>@enderror
<label>Correo</label><input type="email" name="email" value="{{ old('email') }}" required>@error('email')<div class="e">{{ $message }}</div>@enderror
<label>Teléfono (opcional)</label><input name="phone" value="{{ old('phone') }}">
<label>Contraseña</label><input type="password" name="password" required>@error('password')<div class="e">{{ $message }}</div>@enderror
<label>Repite la contraseña</label><input type="password" name="password_confirmation" required>
<p class="m">¿Ya tienes cuenta en Orbis? Usa tu mismo correo y tu contraseña actual.</p>
<button>Enviar solicitud</button></form></div></body></html>
