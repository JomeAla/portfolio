import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, 22, username, password)
    
    print("=== Check if funnel-view.php exists ===")
    stdin, stdout, stderr = ssh.exec_command('ls -la /home/joalacom/public_html/funnel-view.php 2>/dev/null || echo "File not found"')
    print(stdout.read().decode())
    
    print("\n=== Check if it's in public folder ===")
    stdin, stdout, stderr = ssh.exec_command('ls -la /home/joalacom/public_html/public/funnel-view.php 2>/dev/null || echo "File not found"')
    print(stdout.read().decode())
    
    print("\n=== List all php files in public_html root ===")
    stdin, stdout, stderr = ssh.exec_command('ls /home/joalacom/public_html/*.php 2>/dev/null | head -20')
    print(stdout.read().decode())
    
    ssh.close()
except Exception as e:
    print(f"Error: {e}")