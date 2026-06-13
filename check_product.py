import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    transport = paramiko.Transport((host, 22))
    transport.connect(username=username, password=password)
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, username=username, password=password)
    
    # Check the premium zip file contents
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/www && unzip -l storage/app/public/uploads/products/files/wordpress-starter-kit-premium.zip 2>/dev/null || echo "File not found or not a zip"')
    output = stdout.read().decode('utf-8')
    print("PREMIUM PRODUCT CONTENTS:")
    print(output[:2000])
    
    # Also check the product record in database
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/www && php artisan tinker --execute="echo App\\\\Models\\\\Product::where(\'slug\',\'wordpress-starter-kit\')->first();"')
    output2 = stdout.read().decode('utf-8')
    print("\nPRODUCT RECORD:")
    print(output2)
    
    ssh.close()
    transport.close()
    
except Exception as e:
    print(f"Error: {e}")