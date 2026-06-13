import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, 22, username, password)
    
    # Debug the path
    print("=== Test storage_path ===")
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/public_html && php -r "echo storage_path(\'app/public/uploads/products/files/wordpress-starter-kit-free/wordpress-starter-kit-free.zip\');"')
    print("Result: " + stdout.read().decode())
    print("Error: " + stderr.read().decode())
    
    print("\n=== Test file exists ===")
    stdin, stdout, stderr = ssh.exec_command('test -f /home/joalacom/public_html/storage/app/public/uploads/products/files/wordpress-starter-kit-free/wordpress-starter-kit-free.zip && echo "EXISTS" || echo "NOT FOUND"')
    print(stdout.read().decode())
    
    print("\n=== Check storage config ===")
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/public_html && php artisan tinker --execute="echo config(\'filesystems.disks.local.root\');"')
    print(stdout.read().decode())
    
    ssh.close()
except Exception as e:
    print(f"Error: {e}")