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
    
    # Check sequence 21 (the one leads are assigned to)
    print("Sequence 21:")
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/www && php artisan tinker --execute="echo App\\\\Models\\\\EmailSequence::find(21);"')
    output = stdout.read().decode('utf-8')
    print(output)
    
    # Check the landing page configuration
    print("\n\nLanding page 18:")
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/www && php artisan tinker --execute="echo App\\\\Models\\\\LandingPage::find(18);"')
    output2 = stdout.read().decode('utf-8')
    print(output2)
    
    # Check funnel 2 details
    print("\n\nFunnel 2 details:")
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/www && php artisan tinker --execute="echo json_encode(App\\\\Models\\\\Funnel::with(\'stages\')->find(2), JSON_PRETTY_PRINT);"')
    output3 = stdout.read().decode('utf-8')
    print(output3[:5000])
    
    # Check email queue
    print("\n\nEmail queue:")
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/www && php artisan tinker --execute="echo App\\\\Models\\\\EmailQueue::orderBy(\'created_at\',\'desc\')->take(5)->get();"')
    output4 = stdout.read().decode('utf-8')
    print(output4[:3000])
    
    ssh.close()
    transport.close()
    
except Exception as e:
    print(f"Error: {e}")