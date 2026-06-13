import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    transport = paramiko.Transport((host, 22))
    transport.connect(username=username, password=password)
    sftp = paramiko.SFTPClient.from_transport(transport)
    sftp.put('public/test.php', '/home/joalacom/public_html/public/test.php')
    print("Uploaded test.php")
    sftp.close()
    transport.close()
except Exception as e:
    print(f"Error: {e}")