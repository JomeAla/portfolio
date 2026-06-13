import paramiko
import os
import sys
from pathlib import Path

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

local_portfolio = Path('C:/Users/jomea/portfolio')

files_to_upload = [
    ('public/setup_wp_product.php', 'public/setup_wp_product.php'),
]

try:
    transport = paramiko.Transport((host, 22))
    transport.connect(username=username, password=password)
    sftp = paramiko.SFTPClient.from_transport(transport)
    sftp.chdir('/home/joalacom/www')
    
    for local_path, remote_path in files_to_upload:
        if os.path.exists(local_path):
            sftp.put(local_path, remote_path)
            print(f"[OK] Uploaded: {local_path} -> {remote_path}")
        else:
            print(f"[FAIL] File not found: {local_path}")
    
    sftp.close()
    transport.close()
    print("\n[DONE] Setup script uploaded!")
    print("\nRun this on your server:")
    print("  php public/setup_wp_product.php")
    
except Exception as e:
    print(f"Error: {e}")