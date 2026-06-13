import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, 22, username, password)
    
    print("=== Check for sales page files ===")
    stdin, stdout, stderr = ssh.exec_command('ls -la /home/joalacom/public_html/public/wordpress-starter-kit.php 2>/dev/null || echo "Not in public"')
    print(stdout.read().decode())
    
    stdin, stdout, stderr = ssh.exec_command('ls -la /home/joalacom/public_html/wordpress-starter-kit.php 2>/dev/null || echo "Not in root"')
    print(stdout.read().decode())
    
    stdin, stdout, stderr = ssh.exec_command('find /home/joalacom/public_html -name "wordpress-starter-kit.php" 2>/dev/null')
    print(stdout.read().decode())
    
    ssh.close()
except Exception as e:
    print(f"Error: {e}")