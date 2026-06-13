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
    
    # Check recent leads
    print("Recent leads:")
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/www && php artisan tinker --execute="echo App\\\\Models\\\\Lead::orderBy(\'created_at\',\'desc\')->take(5)->get();"')
    output = stdout.read().decode('utf-8')
    print(output[:3000])
    
    # Check funnel stages and sequence
    print("\n\nFunnel 2 (WordPress Starter Kit) stages:")
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/www && php artisan tinker --execute="echo App\\\\Models\\\\Funnel::find(2);"')
    output2 = stdout.read().decode('utf-8')
    print(output2[:2000])
    
    # Check email sequences
    print("\n\nEmail sequences:")
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/www && php artisan tinker --execute="echo App\\\\Models\\\\EmailSequence::where(\'is_active\',1)->get();"')
    output3 = stdout.read().decode('utf-8')
    print(output3[:2000])
    
    ssh.close()
    transport.close()
    
except Exception as e:
    print(f"Error: {e}")