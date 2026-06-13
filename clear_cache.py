import paramiko
import time

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(host, username=username, password=password)
    
    # Clear Laravel cache
    stdin, stdout, stderr = client.exec_command('cd /home/joalacom/www && php artisan cache:clear')
    print('Cache clear output:', stdout.read().decode())
    print('Cache clear errors:', stderr.read().decode())
    
    # Also clear config cache
    stdin, stdout, stderr = client.exec_command('cd /home/joalacom/www && php artisan config:clear')
    print('Config clear output:', stdout.read().decode())
    
    # Clear route cache
    stdin, stdout, stderr = client.exec_command('cd /home/joalacom/www && php artisan route:clear')
    print('Route clear output:', stdout.read().decode())
    
    # Clear view cache
    stdin, stdout, stderr = client.exec_command('cd /home/joalacom/www && php artisan view:clear')
    print('View clear output:', stdout.read().decode())
    
    client.close()
    print("Done!")
except Exception as e:
    print(f"Error: {e}")