import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

# Upload and test scheduler
try:
    transport = paramiko.Transport((host, 22))
    transport.connect(username=username, password=password)
    sftp = paramiko.SFTPClient.from_transport(transport)
    sftp.put('test_scheduler.php', 'test_scheduler.php')
    sftp.close()
    transport.close()
    print("Uploaded test_scheduler.php")
except Exception as e:
    print(f"Upload error: {e}")

# Run it to test
try:
    transport = paramiko.Transport((host, 22))
    transport.connect(username=username, password=password)
    client = transport.open_session()
    client.exec_command('cd /home/joalacom/www && php test_scheduler.php')
    output = client.makefile().read().decode('utf-8', errors='ignore')
    print(output)
    client.close()
    transport.close()
except Exception as e:
    print(f"Run error: {e}")