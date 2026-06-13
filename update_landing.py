import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    transport = paramiko.Transport((host, 22))
    transport.connect(username=username, password=password)
    sftp = paramiko.SFTPClient.from_transport(transport)
    
    print("Uploading routes...")
    sftp.put('routes/web.php', '/home/joalacom/public_html/routes/web.php')
    print("Uploaded")
    
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, 22, username, password)
    
    print("\nTriggering landing page update...")
    stdin, stdout, stderr = ssh.exec_command('curl -s "https://www.joala.com.ng/admin/fix-wp-landing"')
    print(stdout.read().decode())
    print(stderr.read().decode())
    
    sftp.close()
    transport.close()
    ssh.close()
    print("\nDone!")
except Exception as e:
    print(f"Error: {e}")