import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    transport = paramiko.Transport((host, 22))
    transport.connect(username=username, password=password)
    sftp = paramiko.SFTPClient.from_transport(transport)
    
    # Create free folder
    print("Creating free folder...")
    
    # Upload the HTML files
    print("Uploading Setup-Guide.html...")
    sftp.put('storage/app/public/uploads/products/files/wordpress-starter-kit-free/Setup-Guide.html', 
             '/home/joalacom/public_html/storage/app/public/uploads/products/files/wordpress-starter-kit-free/Setup-Guide.html')
    
    print("Uploading SEO-Checklist.html...")
    sftp.put('storage/app/public/uploads/products/files/wordpress-starter-kit-free/SEO-Checklist.html', 
             '/home/joalacom/public_html/storage/app/public/uploads/products/files/wordpress-starter-kit-free/SEO-Checklist.html')
    
    print("Uploading routes/web.php...")
    sftp.put('routes/web.php', '/home/joalacom/public_html/routes/web.php')
    
    print("Uploaded files")
    
    # Create zip file on server
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, 22, username, password)
    
    print("\nCreating zip file...")
    stdin, stdout, stderr = ssh.exec_command(
        'cd /home/joalacom/public_html/storage/app/public/uploads/products/files/wordpress-starter-kit-free && zip -r wordpress-starter-kit-free.zip Setup-Guide.html SEO-Checklist.html'
    )
    print(stdout.read().decode())
    print(stderr.read().decode())
    
    print("\nClearing cache...")
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/public_html && php artisan cache:clear 2>&1')
    print(stdout.read().decode())
    
    sftp.close()
    transport.close()
    ssh.close()
    print("\nDone!")
except Exception as e:
    print(f"Error: {e}")