import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, 22, username, password)
    
    print("=== Resetting to last known good commit (9acb3d7) ===")
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/public_html && git reset --hard 9acb3d7')
    print(stdout.read().decode())
    print(stderr.read().decode())
    
    print("\n=== Clear caches ===")
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/public_html && php artisan cache:clear 2>&1')
    print(stdout.read().decode())
    print(stderr.read().decode())
    
    print("\n=== Checking index.php exists now ===")
    stdin, stdout, stderr = ssh.exec_command('ls -la /home/joalacom/public_html/')
    print(stdout.read().decode())
    
    ssh.close()
    print("\nDone! Please test the site.")
except Exception as e:
    print(f"Error: {e}")