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
    
    # First check sequences table
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/www && php -r "require vendor/autoload.php; $app = require bootstrap/app.php; $app->make(Illuminate\\\\Contracts\\\\Console\\\\Kernel::class)->bootstrap(); echo implode(\'\\n\', Illuminate\\\\Support\\\\Facades\\\\DB::table(\'sequences\')->pluck(\'name\', \'id\')->toArray());"')
    output = stdout.read().decode('utf-8', errors='replace')
    print("sequences table:")
    print(output)
    
    # Set to NULL
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/www && php -r "require vendor/autoload.php; $app = require bootstrap/app.php; $app->make(Illuminate\\\\Contracts\\\\Console\\\\Kernel::class)->bootstrap(); Illuminate\\\\Support\\\\Facades\\\\DB::statement(\\\"UPDATE landing_pages SET sequence_id = NULL WHERE id = 18\\\"); echo \\\"Updated to NULL\\\";"')
    output2 = stdout.read().decode('utf-8', errors='replace')
    print("\nResult:", output2)
    
    ssh.close()
    transport.close()
    
except Exception as e:
    print(f"Error: {e}")