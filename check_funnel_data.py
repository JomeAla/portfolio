import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, 22, username, password)
    
    print("=== Check funnels and their stages ===")
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/public_html && php -r "require \'vendor/autoload.php\'; $pdo = DB::connection()->getPdo(); $stmt = $pdo->query(\'SELECT id, name, welcome_sequence_id, followup_sequence_id FROM funnels\'); while ($row = \$stmt->fetch(PDO::FETCH_ASSOC)) { print_r(\$row); }"')
    print(stdout.read().decode())
    
    print("\n=== Check landing pages ===")
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/public_html && php -r "require \'vendor/autoload.php\'; $pdo = DB::connection()->getPdo(); $stmt = \$pdo->query(\'SELECT id, slug, funnel_id, sequence_id FROM landing_pages\'); while (\$row = \$stmt->fetch(PDO::FETCH_ASSOC)) { print_r(\$row); }"')
    print(stdout.read().decode())
    
    ssh.close()
except Exception as e:
    print(f"Error: {e}")