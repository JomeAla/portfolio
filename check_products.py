import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, 22, username, password)
    
    print("=== Check products for WordPress ===")
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/public_html && php artisan tinker --execute="echo \\App\\Models\\Product::where(\'title\',\'LIKE\',\'%WordPress%\')->orWhere(\'slug\',\'LIKE\',\'%wordpress%\')->get();"')
    print(stdout.read().decode())
    
    ssh.close()
except Exception as e:
    print(f"Error: {e}")