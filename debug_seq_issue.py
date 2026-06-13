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
    
    # Check sequences table
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/www && mysql joalacom_joala -e "SELECT COUNT(*) as cnt FROM sequences" 2>/dev/null')
    output = stdout.read().decode('utf-8', errors='replace')
    print("sequences count:", output.strip())
    
    # Check email_sequences for WP Starter Kit
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/www && mysql joalacom_joala -e "SELECT id, name FROM email_sequences WHERE name LIKE %Welcome%" 2>/dev/null')
    output2 = stdout.read().decode('utf-8', errors='replace')
    print("email_sequences:", output2)
    
    # Insert into sequences table
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/www && mysql joalacom_joala -e "INSERT INTO sequences (name, is_active, created_at, updated_at) VALUES (Welcome WP Starter, 1, NOW(), NOW())" 2>/dev/null')
    print("Insert:", stdout.read().decode('utf-8', errors='replace'))
    
    # Get last insert ID
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/www && mysql joalacom_joala -e "SELECT LAST_INSERT_ID() as lid" 2>/dev/null')
    new_id = stdout.read().decode('utf-8', errors='replace').strip()
    print("New ID:", new_id)
    
    # Update landing page
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/www && mysql joalacom_joala -e "UPDATE landing_pages SET sequence_id = ' + new_id + ' WHERE id = 18" 2>/dev/null')
    print("Updated landing page")
    
    # Verify
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/www && mysql joalacom_joala -e "SELECT id, sequence_id FROM landing_pages WHERE id = 18" 2>/dev/null')
    print("Verification:", stdout.read().decode('utf-8', errors='replace'))
    
    ssh.close()
    transport.close()
    print("\n[DONE]")
    
except Exception as e:
    print(f"Error: {e}")