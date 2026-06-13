import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

# Upload
try:
    transport = paramiko.Transport((host, 22))
    transport.connect(username=username, password=password)
    sftp = paramiko.SFTPClient.from_transport(transport)
    sftp.chdir('/home/joalacom/www')
    sftp.put('check_route.php', 'check_route.php')
    sftp.close()
    transport.close()
    print("Uploaded")
except Exception as e:
    print(f"Upload: {e}")

# Run
try:
    transport = paramiko.Transport((host, 22))
    transport.connect(username=username, password=password)
    client = transport.open_session()
    client.exec_command('php /home/joalacom/www/check_route.php')
    output = client.makefile().read().decode('utf-8', errors='ignore')
    print(output)
    client.close()
    transport.close()
except Exception as e:
    print(f"Run: {e}")