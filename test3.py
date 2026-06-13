import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, 22, username, password)
    
    print("Check where joala.com.ng points to:")
    stdin, stdout, stderr = ssh.exec_command('ls -la /home/joalacom/ | head -20')
    print(stdout.read().decode())
    
    print("\nCheck public_html:")
    stdin, stdout, stderr = ssh.exec_command('ls -la /home/joalacom/public_html/ | head -10')
    print(stdout.read().decode())
    
    print("\nCheck if there's a portfolio folder:")
    stdin, stdout, stderr = ssh.exec_command('ls -la /home/joalacom/public_html/portfolio/ 2>/dev/null | head -10 || echo "No portfolio folder"')
    print(stdout.read().decode())
    
    ssh.close()
except Exception as e:
    print(f"Error: {e}")