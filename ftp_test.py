import paramiko
import os

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

print(f"Connecting to {host}...")

try:
    transport = paramiko.Transport((host, 22))
    transport.connect(username=username, password=password)
    sftp = paramiko.SFTPClient.from_transport(transport)
    print("Connected!")
    
    # Check www directory
    print("\n/www directory:")
    try:
        for d in sftp.listdir('/home/joalacom/www'):
            print(f"  {d}")
    except Exception as e:
        print(f"Error: {e}")
    
    # Check public_html contents
    print("\n/public_html directory:")
    try:
        for d in sftp.listdir('/home/joalacom/public_html'):
            print(f"  {d}")
    except Exception as e:
        print(f"Error: {e}")
    
    sftp.close()
    transport.close()
    
except Exception as e:
    print(f"Error: {e}")