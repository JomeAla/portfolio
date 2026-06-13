import paramiko
import os

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

files_to_upload = [
    ('resources/views/front/wordpress-starter-kit.blade.php', 'resources/views/front/wordpress-starter-kit.blade.php'),
]

try:
    transport = paramiko.Transport((host, 22))
    transport.connect(username=username, password=password)
    sftp = paramiko.SFTPClient.from_transport(transport)
    sftp.chdir('/home/joalacom/www')
    
    for local_path, remote_path in files_to_upload:
        if os.path.exists(local_path):
            sftp.put(local_path, remote_path)
            print(f"[OK] Uploaded: {local_path}")
        else:
            print(f"[FAIL] Not found: {local_path}")
    
    sftp.close()
    transport.close()
    print("\n[DONE] Sales page uploaded!")
    
except Exception as e:
    print(f"Error: {e}")