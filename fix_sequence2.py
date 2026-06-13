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
    
    # Use direct SQL update
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/www && php artisan db:seed --class=Database\\\\Seeders\\\\LandingPageSeeder 2>&1 || php artisan tinker --execute="\\\\DB::statement(\'UPDATE landing_pages SET sequence_id = 67 WHERE id = 18\');"')
    output = stdout.read().decode('utf-8')
    print("SQL update result:", output)
    
    # Check current value
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/www && php artisan db:query "SELECT id, sequence_id, funnel_id FROM landing_pages WHERE id = 18"')
    output2 = stdout.read().decode('utf-8')
    print("Current values:", output2)
    
    ssh.close()
    transport.close()
    
except Exception as e:
    print(f"Error: {e}")