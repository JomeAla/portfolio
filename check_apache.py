import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, 22, username, password)
    
    print("=== Check .htaccess in public_html ===")
    stdin, stdout, stderr = ssh.exec_command('cat /home/joalacom/public_html/.htaccess 2>/dev/null || echo "No .htaccess"')
    print(stdout.read().decode())
    print(stderr.read().decode())
    
    print("\n=== Check .htaccess in public ===")
    stdin, stdout, stderr = ssh.exec_command('cat /home/joalacom/public_html/public/.htaccess 2>/dev/null || echo "No .htaccess"')
    print(stdout.read().decode())
    print(stderr.read().decode())
    
    print("\n=== Check Apache config ===")
    stdin, stdout, stderr = ssh.exec_command('cat /home/joalacom/.nginx.conf 2>/dev/null || cat /etc/apache2/sites-enabled/*.conf 2>/dev/null || echo "Cannot read config"')
    print(stdout.read().decode())
    print(stderr.read().decode())
    
    print("\n=== Check if there's an index.html ===")
    stdin, stdout, stderr = ssh.exec_command('ls -la /home/joalacom/public_html/ | grep -E "index\."')
    print(stdout.read().decode())
    
    ssh.close()
except Exception as e:
    print(f"Error: {e}")