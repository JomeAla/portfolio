import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    transport = paramiko.Transport((host, 22))
    transport.connect(username=username, password=password)
    client = transport.open_session()
    client.exec_command('cd /home/joalacom/www && php add_columns.php')
    output = client.makefile().read()
    error = client.makefile_stderr().read()
    
    print("Output:")
    print(output.decode() if output else "")
    print("Error:")
    print(error.decode() if error else "")
    
    client.close()
    transport.close()
except Exception as e:
    print(f"Error: {e}")