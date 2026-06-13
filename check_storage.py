import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, 22, username, password)
    
    print("=== Check storage folder ===")
    stdin, stdout, stderr = ssh.exec_command('find /home/joalacom/public_html/storage -name "*.zip" 2>/dev/null | head -10')
    print(stdout.read().decode())
    
    print("\n=== Check uploads folder ===")
    stdin, stdout, stderr = ssh.exec_command('find /home/joalacom/public_html -name "wordpress-starter-kit.zip" 2>/dev/null')
    print(stdout.read().decode())
    
    print("\n=== Check public/uploads ===")
    stdin, stdout, stderr = ssh.exec_command('ls -la /home/joalacom/public_html/public/uploads/ 2>/dev/null | head -20')
    print(stdout.read().decode())
    
    ssh.close()
except Exception as e:
    print(f"Error: {e}")