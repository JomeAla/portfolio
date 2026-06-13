import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, 22, username, password)
    
    print("Check Apache config for document root:")
    stdin, stdout, stderr = ssh.exec_command('grep -r "DocumentRoot" /etc/apache2/ 2>/dev/null | head -10')
    print(stdout.read().decode())
    print(stderr.read().decode())
    
    print("\nCheck cpanel domain config:")
    stdin, stdout, stderr = ssh.exec_command('find /var/cpanel/userdata -name "*joala*" -type f 2>/dev/null | head -5')
    print(stdout.read().decode())
    
    print("\nCheck actual domain configuration:")
    stdin, stdout, stderr = ssh.exec_command('cat /etc/apache2/conf.d/userdata/std_2_joalacom/joala.com.ng 2>/dev/null || cat /etc/apache2/sites-enabled/*joala* 2>/dev/null | head -30')
    print(stdout.read().decode())
    
    ssh.close()
except Exception as e:
    print(f"Error: {e}")