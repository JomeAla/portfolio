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
    
    # Execute PHP inline
    php_code = '''php -r "require vendor/autoload.php; $app = require bootstrap/app.php; $app->make(Illuminate\\\\Contracts\\\\Console\\\\Kernel::class)->bootstrap(); Illuminate\\\\Support\\\\Facades\\\\DB::statement('UPDATE landing_pages SET sequence_id = NULL WHERE id = 18'); $f = Illuminate\\\\Support\\\\Facades\\\\DB::table('funnels')->where('id', 2)->first(); if(!$f->welcome_sequence_id) { $seq = Illuminate\\\\Support\\\\Facades\\\\DB::table('email_sequences')->where('name', 'LIKE', '%Welcome%')->first(); if($seq) Illuminate\\\\Support\\\\Facades\\\\DB::table('funnels')->where('id', 2)->update(['welcome_sequence_id' => $seq->id]); } echo 'Done!';"'''
    
    stdin, stdout, stderr = ssh.exec_command(f'cd /home/joalacom/www && {php_code}')
    output = stdout.read().decode('utf-8', errors='replace')
    print(output)
    
    ssh.close()
    transport.close()
    
except Exception as e:
    print(f"Error: {e}")