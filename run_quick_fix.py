import paramiko
import os

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    transport = paramiko.Transport((host, 22))
    transport.connect(username=username, password=password)
    sftp = paramiko.SFTPClient.from_transport(transport)
    sftp.put(r'C:\Users\jomea\portfolio\public\quick_fix.php', 'public/quick_fix.php')
    sftp.close()
    transport.close()
    
    transport2 = paramiko.Transport((host, 22))
    transport2.connect(username=username, password=password)
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, username=username, password=password)
    
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/www && php public/quick_fix.php')
    output = stdout.read().decode('utf-8', errors='replace')
    print(output)
    
    ssh.close()
    transport2.close()
    print("\n[DONE]")
    
except Exception as e:
    print(f"Error: {e}")