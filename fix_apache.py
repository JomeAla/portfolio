import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, 22, username, password)
    
    # Check if there's an .htaccess in public_html that might be overriding
    print("=== Create .htaccess in public_html to redirect to public folder ===")
    htaccess = """<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_URI} !public/
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
"""
    stdin, stdout, stderr = ssh.exec_command(f'echo "{htaccess}" > /home/joalacom/public_html/.htaccess')
    print(stdout.read().decode())
    print(stderr.read().decode())
    
    print("\n=== Clear cache ===")
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/public_html && php artisan cache:clear 2>&1')
    print(stdout.read().decode())
    
    ssh.close()
    print("\nDone! Please test again.")
except Exception as e:
    print(f"Error: {e}")