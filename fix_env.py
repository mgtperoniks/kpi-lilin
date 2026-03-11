import os, base64, secrets

env_path = '/srv/docker/apps/kpi-lilin/.env'

# Read current .env
with open(env_path, 'r') as f:
    lines = f.readlines()

# Strip CRLF and remove ALL APP_KEY lines
lines = [l.rstrip('\r\n') for l in lines]
lines = [l for l in lines if not l.startswith('APP_KEY=')]

# Generate a secure 32-byte key
new_key = 'base64:' + base64.b64encode(secrets.token_bytes(32)).decode('ascii')
print(f'Generated Key: {new_key}')
print(f'Key length (bytes): {len(base64.b64decode(new_key[7:]))}')

# Insert at the top
lines.insert(0, f'APP_KEY={new_key}')

# Write back with Unix line endings
with open(env_path, 'w', newline='\n') as f:
    f.write('\n'.join(lines) + '\n')

print('Done! .env written with LF line endings.')
print('First line:', lines[0])
