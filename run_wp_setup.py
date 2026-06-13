import paramiko
import sys

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    transport = paramiko.Transport((host, 22))
    transport.connect(username=username, password=password)
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, username=username, password=password)
    
    # Run the setup script
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/www && php public/setup_wp_product.php')
    output = stdout.read().decode('utf-8')
    errors = stderr.read().decode('utf-8')
    
    print("OUTPUT:")
    print(output)
    if errors:
        print("ERRORS:")
        print(errors)
    
    ssh.close()
    transport.close()
    
except Exception as e:
    print(f"Error: {e}")