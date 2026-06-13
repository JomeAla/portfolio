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
    
    # Check funnel 2 (WordPress Starter Kit) details
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/www && php -r "require vendor/autoload.php; $app = require bootstrap/app.php; $app->make(Illuminate\\\\Contracts\\\\Console\\\\Kernel::class)->bootstrap(); $f = Illuminate\\\\Support\\\\Facades\\\\DB::table(\'funnels\')->where(\'id\', 2)->first(); print_r($f);"')
    output = stdout.read().decode('utf-8', errors='replace')
    print("Funnel 2:")
    print(output)
    
    # Check the foreign key constraint
    print("\n\nForeign key check:")
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/www && mysql joalacom_joala -e "SHOW CREATE TABLE landing_pages\\G" 2>/dev/null | grep -A5 "sequence_id"')
    output2 = stdout.read().decode('utf-8', errors='replace')
    print(output2)
    
    ssh.close()
    transport.close()
    
except Exception as e:
    print(f"Error: {e}")