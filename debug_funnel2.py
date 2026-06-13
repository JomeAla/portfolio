import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, 22, username, password)
    
    # Check funnel 26 stages
    print("=== Check funnel 26 ===")
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/public_html && php artisan tinker --execute="echo \\App\\Models\\Funnel::with(\'stages\')->find(26);"')
    print(stdout.read().decode())
    print(stderr.read().decode())
    
    # Check landing page
    print("\n=== Check landing page ===")
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/public_html && php artisan tinker --execute="echo \\App\\Models\\LandingPage::where(\'slug\',\'free-wordpress-starter-kit\')->first();"')
    print(stdout.read().decode())
    
    ssh.close()
except Exception as e:
    print(f"Error: {e}")