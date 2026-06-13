import paramiko
ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('joala.com.ng', 22, 'joalacom', '4fu359TgAMi-O+')
sftp = ssh.open_sftp()
content = b'''<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_URI} !^/public/
    RewriteRule ^(.*)$ /public/$1 [L,R=301]
</IfModule>'''
with sftp.file('/home/joalacom/public_html/.htaccess', 'wb') as f:
    f.write(content)
sftp.close()
ssh.close()
print('Done')