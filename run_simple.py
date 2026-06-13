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
    
    # Create the PHP file directly using cat
    php_content = '''<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
use Illuminate\\Support\\Facades\\DB;
echo "<h2>Fix Sequence</h2>";
$seqCount = DB::table('sequences')->count();
echo "<p>sequences: {$seqCount}</p>";
$emailSeq = DB::table('email_sequences')->where('name','LIKE','%Welcome%')->where('name','LIKE','%WP%')->first();
if($emailSeq){
    echo "<p>Email seq: {$emailSeq->id}</p>";
    try{
        $seqId = DB::table('sequences')->insertGetId(['name'=>'Welcome - WP Starter Kit','is_active'=>1,'created_at'=>now(),'updated_at'=>now()]);
        echo "<p>Created seq: {$seqId}</p>";
        DB::table('landing_pages')->where('id',18)->update(['sequence_id'=>$seqId]);
        echo "<p>Updated landing page</p>";
    }catch(Exception $e){
        echo "<p>Error: {$e->getMessage()}</p>";
    }
}
$lp = DB::table('landing_pages')->where('id',18)->first();
echo "<p>Landing page seq: " . ($lp->sequence_id ?? 'NULL') . "</p>";
$funnel = DB::table('funnels')->where('id',2)->first();
echo "<p>Funnel welcome seq: " . ($funnel->welcome_sequence_id ?? 'NULL') . "</p>";
if(empty($funnel->welcome_sequence_id) && $emailSeq){
    DB::table('funnels')->where('id',2)->update(['welcome_sequence_id'=>$emailSeq->id]);
    echo "<p>Updated funnel</p>";
}
'''
    
    # Write file using echo
    stdin, stdout, stderr = ssh.exec_command(f'echo "{php_content}" > /home/joalacom/www/public/fix_seq.php')
    
    # Run it
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/www && php public/fix_seq.php')
    output = stdout.read().decode('utf-8', errors='replace')
    print(output)
    
    ssh.close()
    transport.close()
    
except Exception as e:
    print(f"Error: {e}")