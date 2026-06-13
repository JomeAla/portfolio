import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, 22, username, password)
    
    print("=== Search for any sales page files ===")
    stdin, stdout, stderr = ssh.exec_command('find /home/joalacom -name "*sales*" -type f 2>/dev/null | head -20')
    print(stdout.read().decode())
    
    print("\n=== Check store routes ===")
    stdin, stdout, stderr = ssh.exec_command('grep -r "wordpress-starter-kit" /home/joalacom/public_html/routes/web.php | head -5')
    print(stdout.read().decode())
    
    ssh.close()
except Exception as e:
    print(f"Error: {e}")