import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, 22, username, password)
    
    print("=== Check storage path ===")
    stdin, stdout, stderr = ssh.exec_command('ls -la /home/joalacom/public_html/storage/app/public/uploads/products/files/')
    print(stdout.read().decode())
    print(stderr.read().decode())
    
    print("\n=== Test storage_path function ===")
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/public_html && php -r "echo storage_path(\'app/public/uploads/products/files/wordpress-starter-kit-premium.zip\');"')
    print(stdout.read().decode())
    
    ssh.close()
except Exception as e:
    print(f"Error: {e}")