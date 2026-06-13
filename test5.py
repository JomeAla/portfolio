import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, 22, username, password)
    
    print("Check cpanel domain config:")
    stdin, stdout, stderr = ssh.exec_command('cat /var/cpanel/userdata/joalacom/joala.com.ng | grep -i document')
    print(stdout.read().decode())
    print(stderr.read().decode())
    
    ssh.close()
except Exception as e:
    print(f"Error: {e}")