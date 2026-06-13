import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, 22, username, password)
    
    print("=== Checking public directory ===")
    stdin, stdout, stderr = ssh.exec_command('ls -la /home/joalacom/public_html/public/')
    print(stdout.read().decode())
    
    print("\n=== Checking app directory ===")
    stdin, stdout, stderr = ssh.exec_command('ls -la /home/joalacom/public_html/app/')
    print(stdout.read().decode())
    
    print("\n=== Checking storage directory ===")
    stdin, stdout, stderr = ssh.exec_command('ls -la /home/joalacom/public_html/storage/')
    print(stdout.read().decode())
    
    print("\n=== Checking bootstrap directory ===")
    stdin, stdout, stderr = ssh.exec_command('ls -la /home/joalacom/public_html/bootstrap/')
    print(stdout.read().decode())
    
    ssh.close()
except Exception as e:
    print(f"Error: {e}")