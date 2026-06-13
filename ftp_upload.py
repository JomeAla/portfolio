import paramiko
import os

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

files = [
    'check_freelancer.php',
]

try:
    transport = paramiko.Transport((host, 22))
    transport.connect(username=username, password=password)
    sftp = paramiko.SFTPClient.from_transport(transport)
    sftp.chdir('/home/joalacom/www')
    
    for f in files:
        if os.path.exists(f):
            sftp.put(f, f)
            print(f"Uploaded: {f}")
    
    print("Done!")
    sftp.close()
    transport.close()
except Exception as e:
    print(f"Error: {e}")