import paramiko
import os

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

files = [('public/fix_lead_sequence.php', 'public/fix_lead_sequence.php')]

try:
    transport = paramiko.Transport((host, 22))
    transport.connect(username=username, password=password)
    sftp = paramiko.SFTPClient.from_transport(transport)
    sftp.chdir('/home/joalacom/www')
    
    for local, remote in files:
        sftp.put(local, remote)
        print(f"Uploaded: {local}")
    
    sftp.close()
    transport.close()
    print("Done!")
    
except Exception as e:
    print(f"Error: {e}")