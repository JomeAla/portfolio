from ftplib import FTP

FTP_HOST = 'joala.com.ng'
FTP_USER = 'joalacom'
FTP_PASS = '4fu359TgAMi-O+'
BASE_DIR = '/home/joalacom/public_html/portfolio'

print("Connecting to FTP...")
ftp = FTP(FTP_HOST)
ftp.login(FTP_USER, FTP_PASS)
print("Connected!")

# Try to navigate to portfolio
try:
    ftp.cwd('/home/joalacom/public_html/portfolio')
    print("In portfolio folder")
except:
    try:
        ftp.cwd('/home/joalacom/public_html')
        print("In public_html")
    except:
        print("At root, will create files here")

print("\nUploading files...")

# Helper to upload and create directories
def upload_file(local_path, remote_path):
    parts = remote_path.split('/')
    # Navigate/create directories
    for i in range(len(parts)-1):
        folder = '/'.join(parts[:i+1])
        try:
            ftp.cwd(folder)
        except:
            try:
                ftp.mkd(folder)
                ftp.cwd(folder)
        finally:
            ftp.cwd('/home/joalacom/public_html/portfolio')
    
    # Now upload file
    try:
        with open(local_path, 'rb') as f:
            ftp.storbinary(f'STOR {remote_path}', f)
        print(f"OK: {remote_path}")
    except Exception as e:
        print(f"Error: {e}")

# Upload CustomerController
print("1. CustomerController.php")
upload_file("C:/Users/jomea/portfolio/app/Http/Controllers/Front/CustomerController.php", 
          f"{BASE_DIR}/app/Http/Controllers/Front/CustomerController.php")

# Upload AutomationEngine  
print("2. AutomationEngine.php")
upload_file("C:/Users/jomea/portfolio/app/Services/AutomationEngine.php",
          f"{BASE_DIR}/app/Services/AutomationEngine.php")

# Upload WebhookHub
print("3. WebhookHub.php")
upload_file("C:/Users/jomea/portfolio/app/Services/WebhookHub.php",
          f"{BASE_DIR}/app/Services/WebhookHub.php")

ftp.quit()
print("\n✅ Complete!")