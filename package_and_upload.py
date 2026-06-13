import paramiko
import os
import zipfile
import sys

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

local_folder = r'C:\Users\jomea\portfolio\wp-starter-kit'
zip_path = r'C:\Users\jomea\portfolio\wordpress-starter-kit-premium.zip'

# Create zip file
print("Creating zip file...")
with zipfile.ZipFile(zip_path, 'w', zipfile.ZIP_DEFLATED) as zipf:
    for root, dirs, files in os.walk(local_folder):
        for file in files:
            file_path = os.path.join(root, file)
            arcname = os.path.relpath(file_path, local_folder)
            zipf.write(file_path, arcname)
            print(f"  Added: {arcname}")

print(f"\nZip created: {zip_path}")
print(f"Size: {os.path.getsize(zip_path) / 1024:.1f} KB")

# Upload to server
print("\nUploading to server...")
try:
    transport = paramiko.Transport((host, 22))
    transport.connect(username=username, password=password)
    sftp = paramiko.SFTPClient.from_transport(transport)
    sftp.chdir('/home/joalacom/www')
    
    remote_path = 'storage/app/public/uploads/products/files/wordpress-starter-kit-premium.zip'
    sftp.put(zip_path, remote_path)
    print(f"[OK] Uploaded to: {remote_path}")
    
    # Also upload image as product cover
    cover_path = r'C:\Users\jomea\Desktop\wp starter kit.jpg'
    sftp.put(cover_path, 'public/uploads/products/wordpress-starter-kit-cover.jpg')
    print("[OK] Uploaded product cover image")
    
    sftp.close()
    transport.close()
    print("\n[DONE] Product package uploaded!")
    
except Exception as e:
    print(f"Error: {e}")
    sys.exit(1)