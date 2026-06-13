import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, 22, username, password)
    
    print("=== Check all zip files in public_html ===")
    stdin, stdout, stderr = ssh.exec_command('find /home/joalacom/public_html -name "*.zip" -type f 2>/dev/null | head -20')
    print(stdout.read().decode())
    
    ssh.close()
except Exception as e:
    print(f"Error: {e}")