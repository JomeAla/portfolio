import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    transport = paramiko.Transport((host, 22))
    transport.connect(username=username, password=password)
    sftp = paramiko.SFTPClient.from_transport(transport)
    
    # Check Laravel error log
    try:
        log_content = sftp.file('/home/joalacom/www/storage/logs/laravel.log', 'r').read()
        print("=== Laravel Log (last 100 lines) ===")
        lines = log_content.decode('utf-8', errors='ignore').split('\n')
        for line in lines[-100:]:
            if line.strip():
                print(line)
    except Exception as e:
        print(f"Error reading log: {e}")
    
    # Check error log
    try:
        error_log = sftp.file('/home/joalacom/www/error_log', 'r').read()
        print("\n=== Error Log (last 50 lines) ===")
        lines = error_log.decode('utf-8', errors='ignore').split('\n')
        for line in lines[-50:]:
            if line.strip():
                print(line)
    except Exception as e:
        print(f"Error reading error_log: {e}")
    
    sftp.close()
    transport.close()
except Exception as e:
    print(f"Error: {e}")