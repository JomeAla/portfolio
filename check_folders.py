import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, 22, username, password)
    
    print("=== Check all folders for wordpress starter kit files ===")
    
    # Check lead-magnets folder
    print("\n--- Lead Magnets Folder ---")
    stdin, stdout, stderr = ssh.exec_command('find /home/joalacom/public_html/storage/app/public/lead-magnets -type f 2>/dev/null')
    print(stdout.read().decode())
    
    # Check uploads folder
    print("\n--- Uploads Folder ---")
    stdin, stdout, stderr = ssh.exec_command('find /home/joalacom/public_html/storage/app/public/uploads -type f -name "*wordpress*" 2>/dev/null')
    print(stdout.read().decode())
    
    # Check products folder
    print("\n--- Products Files Folder ---")
    stdin, stdout, stderr = ssh.exec_command('find /home/joalacom/public_html/storage/app/public/products -type f 2>/dev/null')
    print(stdout.read().decode())
    
    ssh.close()
except Exception as e:
    print(f"Error: {e}")