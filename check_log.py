import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, 22, username, password)
    
    # Check Laravel log for recent errors
    print("=== Check Laravel log for errors ===")
    stdin, stdout, stderr = ssh.exec_command('tail -100 /home/joalacom/public_html/storage/logs/laravel.log 2>/dev/null | grep -i "error\|exception\|failed" | tail -30')
    print(stdout.read().decode())
    print(stderr.read().decode())
    
    # Check if the funnel has welcome_sequence_id
    print("\n=== Check funnel 26 (the actual funnel) ===")
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/public_html && php -r "require \'vendor/autoload.php\'; $pdo = DB::connection()->getPdo(); $stmt = $pdo->prepare(\'SELECT id, name, welcome_sequence_id, followup_sequence_id FROM funnels WHERE id = ?\'); $stmt->execute([26]); print_r($stmt->fetch(PDO::FETCH_ASSOC));"')
    print(stdout.read().decode())
    
    ssh.close()
except Exception as e:
    print(f"Error: {e}")