"""Offline deploy regression checks; requires Bash, OpenSSL and Docker Compose CLI."""
import json
import shutil
import subprocess
import tempfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
BASH = shutil.which('bash')


def run(args, **kwargs):
    if 'input' in kwargs:
        kwargs['input'] = kwargs['input'].encode('utf-8')
    result = subprocess.run(args, capture_output=True, **kwargs)
    result.stdout = result.stdout.decode('utf-8')
    result.stderr = result.stderr.decode('utf-8')
    return result


for name in ('deploy.sh', 'golive.sh', 'update.sh', 'docker/entrypoint.sh'):
    result = run([BASH, '-n', str(ROOT / name)])
    assert result.returncode == 0, result.stderr
print('PASS shell syntax')

with tempfile.TemporaryDirectory(prefix='lacdoi-deploy-') as folder:
    work = Path(folder)
    for name in ('deploy.sh', 'docker-compose.deploy.yml'):
        shutil.copy(ROOT / name, work / name)
    envfile = work / '.env.production'
    envfile.write_text('APP_KEY=base64:' + 'A' * 43 + '=\nAPP_URL=https://example.test\nDB_HOST=db\nDB_USERNAME=lakdoi\nDB_DATABASE=shake_hungry\nDB_PASSWORD=test-only\nDB_ROOT_PASSWORD=test-root\n', encoding='utf-8')
    result = run(['docker', 'compose', '--env-file', '.env.production', '-f',
                  'docker-compose.deploy.yml', 'config', '--format', 'json'], cwd=work)
    assert result.returncode == 0, result.stderr
    services = json.loads(result.stdout)['services']
    assert not services['db'].get('ports')
    assert services['app']['environment']['DB_HOST'] == 'db'
    assert services['app']['environment']['DB_USERNAME'] == 'lakdoi'
    assert services['app']['environment']['DB_ROOT_PASSWORD'] == ''
    assert services['app']['environment']['APP_DEBUG'] == 'false'
    assert services['app']['environment']['ADMIN_QUICK_LOGIN'] == 'false'
    assert any(v['type'] == 'bind' and v['target'] == '/var/www/html/data'
               and v['bind']['create_host_path'] is False
               for v in services['app']['volumes'])
    assert services['web']['ports'][0]['published'] == '1113'
    assert services['web']['ports'][0]['host_ip'] == '127.0.0.1'
    print('PASS real Compose configuration')
    binary = work / 'bin'
    binary.mkdir()
    (binary / 'docker').write_text('''#!/usr/bin/env bash
if [[ "$*" == "compose up --help" ]]; then echo --wait-timeout; fi
if [[ "$*" == *"build app web"* && "${MOCK_FAIL:-}" == build ]]; then exit 1; fi
if [[ "$*" == *"--env-file"* && -n "${DB_PASSWORD:-}${DB_ROOT_PASSWORD:-}" ]]; then exit 77; fi
if [[ "$*" == *"up -d"* && "${MOCK_FAIL:-}" == up ]]; then exit 1; fi
exit 0
''', encoding='utf-8')
    (binary / 'curl').write_text('''#!/usr/bin/env bash
[[ "${MOCK_FAIL:-}" != http ]] || exit 1
if [[ "${MOCK_FAIL:-}" == redirect ]]; then printf 302; else printf 200; fi
''', encoding='utf-8')
    # Windows Git Bash lacks flock; concurrency is a VPS/Linux runtime check.
    if __import__('os').name == 'nt':
        (binary / 'flock').write_text('#!/usr/bin/env bash\nexit 0\n', encoding='utf-8')
    for path in binary.iterdir():
        path.chmod(0o700)
    if __import__('os').name == 'nt':
        unix = run([BASH, '-c', 'cygpath -u "$1"', 'test', str(work)]).stdout.strip()
    else:
        unix = str(work)
    prefix = 'export PATH="' + unix + '/bin:$PATH"; '
    original = envfile.read_bytes()
    for failure in ('', 'build', 'up', 'http', 'redirect'):
        result = run([BASH, '-c', prefix + 'export DB_PASSWORD=wrong DB_ROOT_PASSWORD=wrong MOCK_FAIL="' + failure + '"; bash deploy.sh'], cwd=work)
        assert result.returncode == (1 if failure else 0), (result.stdout, result.stderr)
        assert envfile.read_bytes() == original
        if failure:
            assert 'Ứng dụng đã sẵn sàng' not in result.stdout
    print('PASS success / build failure / container failure / HTTP failure / redirect rejected / shell env isolation')
    envfile.unlink()
    (work / '.env.docker').write_text('APP_KEY=base64:' + 'B' * 43 + '=\nDB_USERNAME=root\nDB_PASSWORD=old-secret\n', encoding='utf-8')
    result = run([BASH, '-c', prefix + 'bash deploy.sh'], cwd=work,
                 input='https://example.test\nadmin@example.test\n')
    assert result.returncode == 0, (result.stdout, result.stderr)
    generated = envfile.read_text(encoding='utf-8')
    assert 'APP_KEY=base64:' + 'B' * 43 + '=' in generated
    assert 'DB_USERNAME=lakdoi' in generated and 'DB_HOST=db' in generated
    assert 'old-secret' not in generated
    print('PASS first deploy / preserve APP_KEY / independent credentials')
    envfile.unlink()
    (work / '.env.docker').write_text('APP_KEY=\n', encoding='utf-8')
    result = run([BASH, '-c', prefix + 'bash deploy.sh'], cwd=work, input='https://example.test\n')
    assert result.returncode != 0 and not envfile.exists()
    print('PASS invalid legacy APP_KEY fails without replacing configuration')
