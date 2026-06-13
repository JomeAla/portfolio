import paramiko
import os

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    transport = paramiko.Transport((host, 22))
    transport.connect(username=username, password=password)
    sftp = paramiko.SFTPClient.from_transport(transport)
    sftp.chdir('/home/joalacom/www')
    
    local_path = r'C:\Users\jomea\Desktop\wp starter kit.jpg'
    remote_path = 'public/uploads/products/wordpress-starter-kit-cover.jpg'
    
    if os.path.exists(local_path):
        sftp.put(local_path, remote_path)
        print(f"[OK] Uploaded image to: {remote_path}")
    else:
        print(f"[FAIL] File not found: {local_path}")
    
    sftp.close()
    transport.close()
    print("\n[DONE] Image uploaded!")
    
except Exception as e:
    print(f"Error: {e}")