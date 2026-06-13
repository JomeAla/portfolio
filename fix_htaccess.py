import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, 22, username, password)
    
    # Update .htaccess to exclude .php files from redirect
    htaccess = """<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_URI} !public/
    RewriteCond %{REQUEST_URI} !\\.php$
    RewriteRule ^(.*)$ public/ [L]
</IfModule>
"""
    stdin, stdout, stderr = ssh.exec_command(f'echo "{htaccess}" > /home/joalacom/public_html/.htaccess')
    print("Updated .htaccess")
    
    # Clear Laravel cache
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/public_html && php artisan cache:clear 2>&1')
    print(stdout.read().decode())
    
    ssh.close()
    print("Done!")
except Exception as e:
    print(f"Error: {e}")