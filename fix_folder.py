import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, 22, username, password)
    
    print("=== Create lead-magnets folder structure ===")
    
    # Create proper folder structure
    stdin, stdout, stderr = ssh.exec_command('mkdir -p /home/joalacom/public_html/storage/app/public/lead-magnets/wordpress-starter-kit')
    print("Created directory")
    
    # Copy the files
    stdin, stdout, stderr = ssh.exec_command('cp /home/joalacom/public_html/storage/app/public/uploads/products/files/wordpress-starter-kit-free/* /home/joalacom/public_html/storage/app/public/lead-magnets/wordpress-starter-kit/')
    print("Copied files")
    
    # List the new folder
    print("\n=== List lead-magnets folder ===")
    stdin, stdout, stderr = ssh.exec_command('ls -la /home/joalacom/public_html/storage/app/public/lead-magnets/wordpress-starter-kit/')
    print(stdout.read().decode())
    
    # Test the file path
    print("\n=== Test path ===")
    stdin, stdout, stderr = ssh.exec_command('test -f /home/joalacom/public_html/storage/app/public/lead-magnets/wordpress-starter-kit/wordpress-starter-kit-free.zip && echo "EXISTS" || echo "NOT FOUND"')
    print(stdout.read().decode())
    
    # Test using base_path like Laravel would
    print("\n=== Test base_path ===")
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/public_html && php -r "echo base_path(\'storage/app/public/lead-magnets/wordpress-starter-kit/wordpress-starter-kit-free.zip\');"')
    result = stdout.read().decode()
    print("Path: " + result)
    
    stdin, stdout, stderr = ssh.exec_command('test -f "' + result.strip() + '" && echo "FILE WORKS" || echo "FILE NOT FOUND"')
    print(stdout.read().decode())
    
    ssh.close()
except Exception as e:
    print(f"Error: {e}")