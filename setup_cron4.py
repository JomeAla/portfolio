import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    transport = paramiko.Transport((host, 22))
    transport.connect(username=username, password=password)
    sftp = paramiko.SFTPClient.from_transport(transport)
    
    # Upload crontab file
    sftp.put('crontab.txt', '/home/joalacom/crontab.txt')
    sftp.close()
    transport.close()
    print("Uploaded crontab.txt")
except Exception as e:
    print(f"Upload error: {e}")

# Now set it
try:
    transport = paramiko.Transport((host, 22))
    transport.connect(username=username, password=password)
    client = transport.open_session()
    
    # Import the crontab
    client.exec_command('crontab /home/joalacom/crontab.txt')
    result = client.makefile().read().decode('utf-8', errors='ignore')
    error = client.makefile_stderr().read().decode('utf-8', errors='ignore')
    
    print("Import result:", result)
    print("Error:", error if error else "None")
    
    # Verify
    client.exec_command('crontab -l')
    new_cron = client.makefile().read().decode('utf-8', errors='ignore')
    print("\nCurrent crontab:")
    print(new_cron)
    
    client.close()
    transport.close()
    print("\nDone!")
except Exception as e:
    print(f"Import error: {e}")