import paramiko
import os

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'
remote_path = '/home/joalacom/public_html/portfolio/.htaccess'

local_file = r'C:\Users\jomea\portfolio\.htaccess'

try:
    print("Connecting to server...")
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, 22, username, password)
    
    print("Uploading .htaccess...")
    sftp = ssh.open_sftp()
    sftp.put(local_file, remote_path)
    sftp.close()
    
    print("Verifying upload...")
    stdin, stdout, stderr = ssh.exec_command(f'cat {remote_path}')
    print("File content:")
    print(stdout.read().decode())
    
    print("\nDone! .htaccess uploaded successfully.")
    ssh.close()
except Exception as e:
    print(f"Error: {e}")