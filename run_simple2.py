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
    
    # Simple approach: Set landing page sequence to NULL, set funnel welcome_sequence_id
    commands = [
        # Set landing page sequence_id to NULL 
        'cd /home/joalacom/www && php -r "require vendor/autoload.php; $app = require bootstrap/app.php; $app->make(Illuminate\\\\Contracts\\\\Console\\\\Kernel::class)->bootstrap(); Illuminate\\\\Support\\\\Facades\\\\DB::statement(\\\"UPDATE landing_pages SET sequence_id = NULL WHERE id = 18\\\"); echo \\\"LP set to NULL\\\\n\\\";"',
        
        # Get the WP Starter Kit email sequence ID
        'cd /home/joalacom/www && php -r "require vendor/autoload.php; $app = require bootstrap/app.php; $app->make(Illuminate\\\\Contracts\\\\Console\\\\Kernel::class)->bootstrap(); $seq = Illuminate\\\\Support\\\\Facades\\\\DB::table(\\\\"email_sequences\\\")->where(\\\\"name\\\",\\\"like\\\",\\\"%Welcome%\\\")->where(\\\\"name\\\",\\\"like\\\",\\\"%WP%\\\")->first(); echo $seq ? $seq->id : \\\"NOT FOUND\\\";"',
    ]
    
    for cmd in commands:
        stdin, stdout, stderr = ssh.exec_command(cmd)
        output = stdout.read().decode('utf-8', errors='replace')
        print(output)
    
    # Set funnel welcome_sequence_id to 67
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/www && php -r "require vendor/autoload.php; $app = require bootstrap/app.php; $app->make(Illuminate\\\\Contracts\\\\Console\\\\Kernel::class)->bootstrap(); Illuminate\\\\Support\\\\Facades\\\\DB::statement(\\\"UPDATE funnels SET welcome_sequence_id = 67 WHERE id = 2\\\"); echo \\\"Funnel updated to seq 67\\\\n\\\";"')
    output = stdout.read().decode('utf-8', errors='replace')
    print(output)
    
    # Verify
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/www && php -r "require vendor/autoload.php; $app = require bootstrap/app.php; $app->make(Illuminate\\\\Contracts\\\\Console\\\\Kernel::class)->bootstrap(); $lp = Illuminate\\\\Support\\\\Facades\\\\DB::table(\\\\"landing_pages\\\")->where(\\\\"id\\\",18)->first(); $fn = Illuminate\\\\Support\\\\Facades\\\\DB::table(\\\\"funnels\\\")->where(\\\\"id\\\",2)->first(); echo \\\"LP seq: \\\" . ($lp->sequence_id ?? \\\"NULL\\\") . \\\" | Funnel welcome: \\\" . ($fn->welcome_sequence_id ?? \\\"NULL\\\");"')
    output = stdout.read().decode('utf-8', errors='replace')
    print("\nFinal:", output)
    
    ssh.close()
    transport.close()
    print("\n[DONE]")
    
except Exception as e:
    print(f"Error: {e}")