#!/usr/bin/env python3
import os
import sys
from ftplib import FTP

# FTP Credentials from CREDENTIALS.md
FTP_HOST = 'joala.com.ng'
FTP_USER = 'joalacom'
FTP_PASS = '4fu359TgAMi-O+'
LIVE_PATH = '/home/joalacom/public_html/portfolio'

# Files to exclude from upload
EXCLUDE_DIRS = ['node_modules', '.git', 'vendor', 'uploads', '.vscode']
EXCLUDE_FILES = ['.env', '.gitignore', '*.log', 'composer.lock', 'package-lock.json']

def should_upload(path):
    for d in EXCLUDE_DIRS:
        if d in path.split('/'):
            return False
    return True

def upload_dir(ftp, local_dir, remote_dir):
    if not os.path.isdir(local_dir):
        return
    
    # Create remote directory if it doesn't exist
    try:
        ftp.cwd(remote_dir)
    except:
        try:
            ftp.mkd(remote_dir)
            ftp.cwd(remote_dir)
        except:
            pass
    
    # Upload all files
    for item in os.listdir(local_dir):
        local_path = os.path.join(local_dir, item)
        remote_path = f"{remote_dir}/{item}"
        
        # Skip excluded
        if not should_upload(local_path):
            print(f"  Skipping: {item}")
            continue
        
        if os.path.isdir(local_path):
            try:
                ftp.mkd(remote_path)
            except:
                pass
            ftp.cwd(remote_path)
            upload_dir(ftp, local_path, remote_path)
            ftp.cwd('..')
        else:
            # Upload file
            ext = os.path.splitext(item)[1]
            if ext in ['.php', '.js', '.css', '.html', '.blade.php', '.json', '.md', '.txt']:
                print(f"  Uploading: {item}")
                try:
                    with open(local_path, 'rb') as f:
                        ftp.storbinary(f'STOR {remote_path}', f)
                except Exception as e:
                    print(f"    Error: {e}")

def main():
    print("🚀 Uploading to live server...")
    print(f"  Host: {FTP_HOST}")
    print(f"  Path: {LIVE_PATH}")
    
    try:
        ftp = FTP(FTP_HOST)
        ftp.login(FTP_USER, FTP_PASS)
        print("✅ Connected!")
        
        # Navigate to portfolio folder
        try:
            ftp.cwd(LIVE_PATH)
        except:
            print(f"❌ Cannot navigate to {LIVE_PATH}")
            return
        
        print("📤 Uploading files...")
        
        # Upload key directories and files
        dirs_to_upload = ['app', 'resources/views', 'routes', 'public']
        for d in dirs_to_upload:
            local_path = f"C:/Users/jomea/portfolio/{d}"
            if os.path.exists(local_path):
                print(f"\n  📁 {d}/")
                ftp.cwd(LIVE_PATH)
                
                try:
                    ftp.mkd(d)
                except:
                    pass
                ftp.cwd(d)
                upload_dir(ftp, local_path, d)
                ftp.cwd('..')
        
        # Upload root files
        print("\n  📄 Root files:")
        root_files = ['routes/web.php', 'routes/payment.php']
        for f in root_files:
            local_path = f"C:/Users/jomea/portfolio/{f}"
            if os.path.exists(local_path):
                print(f"    Uploading: {f}")
                with open(local_path, 'rb') as file:
                    ftp.storbinary(f'STOR {f}', file)
        
        ftp.quit()
        print("\n✅ Upload complete!")
        print("\n📋 Next steps:")
        print("1. Visit https://joala.com.ng/customer/register")
        print("2. Visit https://joala.com.ng/customer/affiliate")
        print("3. Visit https://joala.com.ng/admin/marketing")
        
    except Exception as e:
        print(f"❌ Error: {e}")

if __name__ == '__main__':
    main()