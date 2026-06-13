import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, 22, username, password)
    
    print("=== Check current folder structure ===")
    
    # Check lead-magnets folder
    print("\n--- Lead Magnets (free) ---")
    stdin, stdout, stderr = ssh.exec_command('find /home/joalacom/public_html/storage/app/public/lead-magnets -type f -name "*.zip" -o -name "*.html" 2>/dev/null')
    print(stdout.read().decode())
    
    # Check products folder
    print("\n--- Products (premium) ---")
    stdin, stdout, stderr = ssh.exec_command('find /home/joalacom/public_html/storage/app/public/products -type f -name "*.zip" 2>/dev/null')
    print(stdout.read().decode())
    
    # Check old uploads folder (for legacy)
    print("\n--- Legacy uploads ---")
    stdin, stdout, stderr = ssh.exec_command('find /home/joalacom/public_html/storage/app/public/uploads/products/files -type f -name "*.zip" 2>/dev/null')
    print(stdout.read().decode())
    
    # Summary
    print("\n=== Folder Structure Summary ===")
    print("Lead Magnets (free): /storage/app/public/lead-magnets/")
    print("  - wordpress-starter-kit/ (free WordPress Starter Kit)")
    print("")
    print("Products (premium): /storage/app/public/products/files/")
    print("  - (premium products)")
    print("")
    print("Legacy: /storage/app/public/uploads/products/files/")
    print("  - (old premium files for backwards compatibility)")
    
    ssh.close()
except Exception as e:
    print(f"Error: {e}")