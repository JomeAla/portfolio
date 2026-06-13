import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    transport = paramiko.Transport((host, 22))
    transport.connect(username=username, password=password)
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, username=username, password=password)
    
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/www && php public/fix_lead_sequence.php')
    output = stdout.read().decode('utf-8', errors='replace')
    print(output)
    
    ssh.close()
    transport.close()
    
except Exception as e:
    print(f"Error: {e}")