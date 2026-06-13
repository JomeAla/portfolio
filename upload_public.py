import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    print("Uploading funnel-view.php to public folder...")
    transport = paramiko.Transport((host, 22))
    transport.connect(username=username, password=password)
    sftp = paramiko.SFTPClient.from_transport(transport)
    sftp.put('public/funnel-view.php', '/home/joalacom/public_html/public/funnel-view.php')
    print("Done!")
    sftp.close()
    transport.close()
except Exception as e:
    print(f"Error: {e}")