import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, 22, username, password)
    
    print("=== Checking for index.php in git history ===")
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/public_html && git log --all --oneline -- index.php | head -10')
    print(stdout.read().decode())
    print(stderr.read().decode())
    
    print("\n=== Checking for any backup files ===")
    stdin, stdout, stderr = ssh.exec_command('find /home/joalacom -name "index.php" -type f 2>/dev/null | head -10')
    print(stdout.read().decode())
    
    ssh.close()
except Exception as e:
    print(f"Error: {e}")