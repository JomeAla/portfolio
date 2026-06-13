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
    sftp.close()
    transport.close()
    print("Uploaded")
except Exception as e:
    print(f"Upload: {e}")

# Run add columns
try:
    transport = paramiko.Transport((host, 22))
    transport.connect(username=username, password=password)
    client = transport.open_session()
    client.exec_command('cd /home/joalacom/www && php add_columns.php')
    output = client.makefile().read().decode('utf-8', errors='ignore')
    print(output)
    client.close()
    transport.close()
except Exception as e:
    print(f"Add cols: {e}")

# Run setup
try:
    transport = paramiko.Transport((host, 22))
    transport.connect(username=username, password=password)
    client = transport.open_session()
    client.exec_command('cd /home/joalacom/www && php setup_cart_abandonment.php')
    output = client.makefile().read().decode('utf-8', errors='ignore')
    print(output)
    client.close()
    transport.close()
except Exception as e:
    print(f"Setup: {e}")