import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

remote_path = '/home/joalacom/public_html'

try:
    print("Connecting to server...")
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, 22, username, password)
    
    print("Stashing local changes...")
    stdin, stdout, stderr = ssh.exec_command(f'cd {remote_path} && git stash -u 2>&1')
    result = stdout.read().decode()
    print(result)
    print(stderr.read().decode())
    
    print("\nGit pull...")
    stdin, stdout, stderr = ssh.exec_command(f'cd {remote_path} && git pull origin master 2>&1')
    print(stdout.read().decode())
    print(stderr.read().decode())
    
    print("\nClearing caches...")
    stdin, stdout, stderr = ssh.exec_command(f'cd {remote_path} && php artisan cache:clear 2>&1')
    print(stdout.read().decode())
    print(stderr.read().decode())
    
    print("\nDone!")
    ssh.close()
except Exception as e:
    print(f"Error: {e}")