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
    
    # Try to insert into sequences table directly
    cmd = '''cd /home/joalacom/www && php -r "
require vendor/autoload.php;
$app = require bootstrap/app.php;
$app->make(Illuminate\\\\Contracts\\\\Console\\\\Kernel::class)->bootstrap();
use Illuminate\\\\Support\\\\Facades\\\\DB;

// Check sequences table
$seqs = DB::table('sequences')->get();
echo 'sequences table count: ' . count($seqs) . PHP_EOL;

// Try insert
try {
    $id = DB::table('sequences')->insertGetId([
        'name' => 'Welcome - WP Starter Kit',
        'is_active' => 1,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    echo 'Inserted sequence with ID: ' . $id . PHP_EOL;
    
    // Update landing page
    DB::table('landing_pages')->where('id', 18)->update(['sequence_id' => $id]);
    echo 'Updated landing page sequence_id' . PHP_EOL;
} catch(Exception \\$e) {
    echo 'Error: ' . \\$e->getMessage() . PHP_EOL;
}
"
'''
    stdin, stdout, stderr = ssh.exec_command(cmd)
    output = stdout.read().decode('utf-8', errors='replace')
    print(output)
    
    ssh.close()
    transport.close()
    
except Exception as e:
    print(f"Error: {e}")