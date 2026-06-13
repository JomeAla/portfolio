import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, 22, username, password)
    
    print("=== Check git reflog ===")
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/public_html && git reflog | head -10')
    print(stdout.read().decode())
    
    print("\n=== Check git status ===")
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/public_html && git status')
    print(stdout.read().decode())
    
    print("\n=== Check main site backup - look for old commits ===")
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/public_html && git log --all --oneline | head -20')
    print(stdout.read().decode())
    
    ssh.close()
except Exception as e:
    print(f"Error: {e}")