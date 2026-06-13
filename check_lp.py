import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, 22, username, password)
    
    print("=== Check landing page in database ===")
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/public_html && php artisan tinker --execute="echo App\\\\Models\\\\LandingPage::where(\'slug\', \'free-wordpress-starter-kit\')->first();"')
    result = stdout.read().decode()
    print(result)
    
    print("\n=== Check landing page details ===")
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/public_html && php -r "require \'vendor/autoload.php\'; $app = require_once \'bootstrap/app.php\'; $pdo = DB::connection()->getPdo(); $stmt = $pdo->prepare(\'SELECT * FROM landing_pages WHERE slug = ?\'); $stmt->execute([\'free-wordpress-starter-kit\']); print_r($stmt->fetch(PDO::FETCH_ASSOC));"')
    print(stdout.read().decode())
    print(stderr.read().decode())
    
    ssh.close()
except Exception as e:
    print(f"Error: {e}")