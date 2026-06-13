import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    transport = paramiko.Transport((host, 22))
    transport.connect(username=username, password=password)
    sftp = paramiko.SFTPClient.from_transport(transport)
    
    sftp.put('routes/web.php', '/home/joalacom/public_html/routes/web.php')
    print("Uploaded routes")
    
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, 22, username, password)
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/public_html && php artisan cache:clear 2>&1')
    print(stdout.read().decode())
    
    sftp.close()
    transport.close()
    ssh.close()
    print("Done!")
except Exception as e:
    print(f"Error: {e}")