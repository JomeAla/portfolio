import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    transport = paramiko.Transport((host, 22))
    transport.connect(username=username, password=password)
    sftp = paramiko.SFTPClient.from_transport(transport)
    sftp.chdir('/home/joalacom/www')
    sftp.put('routes/web.php', 'routes/web.php')
    sftp.close()
    transport.close()
    print("Uploaded routes/web.php")
except Exception as e:
    print(f"Error: {e}")