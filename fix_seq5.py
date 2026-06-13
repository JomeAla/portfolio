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
    
    # Check current sequence IDs
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/www && php -r "require \'vendor/autoload.php\'; $app = require_once \'bootstrap/app.php\'; $app->make(\'Illuminate\\\\Contracts\\\\Console\\\\Kernel\')->bootstrap(); $pdo = DB::connection()->getPdo(); $stmt = $pdo->query(\'SELECT id, name FROM email_sequences ORDER BY id DESC LIMIT 10\'); while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { echo $row[\'id\'] . \': \' . $row[\'name\'] . \"\\n\"; }"')
    output = stdout.read().decode('utf-8', errors='replace')
    print("Sequences in DB:")
    print(output)
    
    # Try sequence 5 (Cart Abandonment - exists)
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/www && php artisan model:show App\\\\Models\\\\LandingPage 2>&1 | head -3')
    output2 = stdout.read().decode('utf-8')
    
    # Just update it
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/www && php -r "require \'vendor/autoload.php\'; $app = require_once \'bootstrap/app.php\'; $app->make(\'Illuminate\\\\Contracts\\\\Console\\\\Kernel\')->bootstrap(); DB::connection()->getPdo()->exec(\'UPDATE landing_pages SET sequence_id = 5 WHERE id = 18\'); echo \'Updated!\';"')
    output3 = stdout.read().decode('utf-8')
    print("\nUpdate result:", output3)
    
    # Verify
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/www && php -r "require \'vendor/autoload.php\'; $app = require_once \'bootstrap/app.php\'; $app->make(\'Illuminate\\\\Contracts\\\\Console\\\\Kernel\')->bootstrap(); $pdo = DB::connection()->getPdo(); $stmt = $pdo->query(\'SELECT sequence_id FROM landing_pages WHERE id = 18\'); echo $stmt->fetchColumn();"')
    output4 = stdout.read().decode('utf-8')
    print("Verified sequence_id:", output4)
    
    ssh.close()
    transport.close()
    
except Exception as e:
    print(f"Error: {e}")