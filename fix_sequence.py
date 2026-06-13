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
    
    # Update landing page to use correct sequence (67)
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/www && php artisan tinker --execute="App\\\\Models\\\\LandingPage::where(\'id\',18)->update([\'sequence_id\' => 67]);"')
    output = stdout.read().decode('utf-8')
    print("Updated landing page sequence_id to 67")
    
    # Verify
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/www && php artisan tinker --execute="echo App\\\\Models\\\\LandingPage::find(18)->sequence_id;"')
    output2 = stdout.read().decode('utf-8')
    print("Verified sequence_id:", output2.strip())
    
    # Check sequence 67 steps
    print("\nSequence 67 steps:")
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/www && php artisan tinker --execute="echo App\\\\Models\\\\SequenceStep::where(\'sequence_id\',67)->orderBy(\'step_order\')->get();"')
    output3 = stdout.read().decode('utf-8')
    print(output3[:2000])
    
    ssh.close()
    transport.close()
    print("\n[DONE] Fixed!")
    
except Exception as e:
    print(f"Error: {e}")