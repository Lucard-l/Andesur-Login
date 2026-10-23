"""Prueba HTTP: python tests/smoke.py [URL]. Añade dos accesos de Ana."""
import http.cookiejar
import re
import sys
import urllib.error
import urllib.parse
import urllib.request

base = sys.argv[1] if len(sys.argv) > 1 else 'http://127.0.0.1:8080'
client = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))

def get(path='/'):
    return client.open(base + path).read().decode()

def post(fields):
    return client.open(base + '/', urllib.parse.urlencode(fields).encode()).read().decode()

def token(html):
    return re.search(r'name="csrf" value="([a-f0-9]+)"', html)[1]

page = get()
assert 'Inicia sesión' in page
try:
    post({'email': 'ana@andesur.test', 'password': 'P@ssw0rd', 'csrf': 'invalid'})
    raise AssertionError('CSRF aceptado')
except urllib.error.HTTPError as error:
    assert error.code == 403
page = post({'csrf': token(page), 'email': 'ana@andesur.test', 'password': 'incorrecta'})
assert 'Correo o contraseña incorrectos' in page
for _ in range(2):
    page = post({'csrf': token(page), 'email': 'ana@andesur.test', 'password': 'P@ssw0rd'})
    assert 'Accesos del día' in page and 'Ana Torres' in page
    assert re.search(r'\d{2}:\d{2}:\d{2}', page)
    old = get('/?day=2000-01-01')
    assert 'Todavía no hay accesos' in old
    page = post({'csrf': token(page), 'action': 'logout'})
    assert 'Inicia sesión' in page
assert 'Inicia sesión' in get()
for path in ['/database/02-seed.sql', '/compose.yaml', '/.git/config']:
    try:
        get(path)
        raise AssertionError('Archivo privado accesible: ' + path)
    except urllib.error.HTTPError as error:
        assert error.code == 404
print('OK: login, rechazo de credenciales y CSRF, fechas, segundos, logout y archivos privados.')
