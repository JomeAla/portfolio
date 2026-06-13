import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, 22, username, password)
    
    print("=== Clear all caches ===")
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/public_html && php artisan cache:clear && php artisan route:clear && php artisan view:clear 2>&1')
    print(stdout.read().decode())
    print(stderr.read().decode())
    
    print("\n=== Check config ===")
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/public_html && php artisan tinker --execute="echo config(\'filesystems.disks.local.root\');"')
    print(stdout.read().decode())
    
    ssh.close()
except Exception as e:
    print(f"Error: {e}")