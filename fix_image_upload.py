import paramiko
import os

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

cover_path = r'C:\Users\jomea\Desktop\wp starter kit.jpg'

try:
    transport = paramiko.Transport((host, 22))
    transport.connect(username=username, password=password)
    sftp = paramiko.SFTPClient.from_transport(transport)
    
    # Check current path
    print("Current dir:", sftp.getcwd())
    
    # Upload to correct public folder
    remote_path = 'public_html/public/uploads/products/wordpress-starter-kit-cover.jpg'
    sftp.put(cover_path, remote_path)
    print(f"[OK] Uploaded to: {remote_path}")
    
    sftp.close()
    transport.close()
    print("\n[DONE] Image uploaded!")
    
except Exception as e:
    print(f"Error: {e}")
    import traceback
    traceback.print_exc()