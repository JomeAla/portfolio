import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, 22, username, password)
    
    print("=== Checking public_html ===")
    stdin, stdout, stderr = ssh.exec_command('ls -la /home/joalacom/public_html/ | head -20')
    print(stdout.read().decode())
    
    print("\n=== Checking index.php ===")
    stdin, stdout, stderr = ssh.exec_command('head -20 /home/joalacom/public_html/index.php')
    print(stdout.read().decode())
    
    print("\n=== Checking .htaccess ===")
    stdin, stdout, stderr = ssh.exec_command('cat /home/joalacom/public_html/.htaccess')
    print(stdout.read().decode())
    
    ssh.close()
except Exception as e:
    print(f"Error: {e}")