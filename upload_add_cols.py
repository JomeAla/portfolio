import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    transport = paramiko.Transport((host, 22))
    transport.connect(username=username, password=password)
    sftp = paramiko.SFTPClient.from_transport(transport)
    sftp.chdir('/home/joalacom/www')
    sftp.put('add_columns.php', 'add_columns.php')
    print("Uploaded add_columns.php")
    sftp.close()
    transport.close()
except Exception as e:
    print(f"Error: {e}")