import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

files_to_upload = [
    'app/Http/Controllers/Admin/MarketingController.php',
    'resources/views/admin/marketing/funnels/edit.blade.php',
    'resources/views/admin/marketing/sequences/edit.blade.php',
    'routes/web.php',
]

remote_path = '/home/joalacom/public_html'

try:
    print("Connecting to server...")
    transport = paramiko.Transport((host, 22))
    transport.connect(username=username, password=password)
    sftp = paramiko.SFTPClient.from_transport(transport)
    
    print("Uploading files...")
    for f in files_to_upload:
        print(f"  Uploading {f}...")
        sftp.put(f, remote_path + '/' + f)
    
    print("\nClearing caches...")
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, 22, username, password)
    stdin, stdout, stderr = ssh.exec_command(f'cd {remote_path} && php artisan cache:clear 2>&1')
    print(stdout.read().decode())
    
    print("\nDone! Files uploaded successfully.")
    sftp.close()
    transport.close()
    ssh.close()
except Exception as e:
    print(f"Error: {e}")