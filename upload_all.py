import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    transport = paramiko.Transport((host, 22))
    transport.connect(username=username, password=password)
    sftp = paramiko.SFTPClient.from_transport(transport)
    
    print("Uploading files...")
    sftp.put('app/Http/Controllers/Admin/MarketingController.php', '/home/joalacom/public_html/app/Http/Controllers/Admin/MarketingController.php')
    sftp.put('routes/web.php', '/home/joalacom/public_html/routes/web.php')
    sftp.put('resources/views/front/landing_page.blade.php', '/home/joalacom/public_html/resources/views/front/landing_page.blade.php')
    print("Uploaded controller, routes, and landing page")
    
    # Create the download page directory if needed
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, 22, username, password)
    
    # Create the view directory and upload the file
    print("\nUploading download page view...")
    stdin, stdout, stderr = ssh.exec_command('mkdir -p /home/joalacom/public_html/resources/views/front')
    sftp.put('resources/views/front/download-page.blade.php', '/home/joalacom/public_html/resources/views/front/download-page.blade.php')
    print("Uploaded download page")
    
    # Clear caches
    print("\nClearing caches...")
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/public_html && php artisan cache:clear && php artisan view:clear 2>&1')
    print(stdout.read().decode())
    
    sftp.close()
    transport.close()
    ssh.close()
    print("\nDone!")
except Exception as e:
    print(f"Error: {e}")