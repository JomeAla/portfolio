import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, 22, username, password)
    
    print("=== Create directory and files ===")
    
    # Create the directory
    stdin, stdout, stderr = ssh.exec_command('mkdir -p /home/joalacom/public_html/storage/app/public/uploads/products/files/wordpress-starter-kit-free')
    print("Created directory")
    
    # Create Setup Guide HTML file
    setup_guide = '''<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WordPress Setup Guide</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, sans-serif; line-height: 1.6; max-width: 800px; margin: 0 auto; padding: 20px; }
        h1 { color: #1a1a1a; border-bottom: 2px solid #3b82f6; padding-bottom: 10px; }
        h2 { color: #374151; margin-top: 30px; }
        .step { background: #f9fafb; border: 1px solid #e5e7eb; padding: 20px; margin: 15px 0; border-radius: 8px; }
        ol, ul { padding-left: 20px; }
        li { margin: 8px 0; }
    </style>
</head>
<body>
    <h1>WordPress Setup Guide</h1>
    <p>Follow this step-by-step guide to set up your WordPress site.</p>
    
    <h2>Step 1: Choose Your Hosting</h2>
    <div class="step">
        <ul>
            <li><strong>SiteGround</strong> - Great for beginners</li>
            <li><strong>HostGator</strong> - Affordable</li>
            <li><strong>Bluehost</strong> - WordPress recommended</li>
        </ul>
    </div>
    
    <h2>Step 2: Install WordPress</h2>
    <div class="step">
        <ol>
            <li>Log in to your hosting account</li>
            <li>Find "WordPress Installer"</li>
            <li>Click install and follow prompts</li>
            <li>Note your admin login details</li>
        </ol>
    </div>
    
    <h2>Step 3: Choose a Theme</h2>
    <div class="step">
        <p><strong>Free Themes:</strong></p>
        <ul>
            <li><strong>Astra</strong> - Lightweight, fast</li>
            <li><strong>GeneratePress</strong> - Simple, fast</li>
            <li><strong>Kadence</strong> - Modern, feature-rich</li>
        </ul>
    </div>
    
    <h2>Step 4: Install Essential Plugins</h2>
    <div class="step">
        <ul>
            <li><strong>Yoast SEO</strong> - Search engine optimization</li>
            <li><strong>Elementor</strong> - Page builder</li>
            <li><strong>WPForms</strong> - Contact forms</li>
            <li><strong>Wordfence</strong> - Security</li>
        </ul>
    </div>
    
    <h2>Step 5: Create Your Pages</h2>
    <div class="step">
        <ul>
            <li><strong>Home</strong> - Your main landing page</li>
            <li><strong>About</strong> - Who you are</li>
            <li><strong>Services</strong> - What you offer</li>
            <li><strong>Contact</strong> - How to reach you</li>
        </ul>
    </div>
    
    <h2>Step 6: Configure SEO</h2>
    <div class="step">
        <ol>
            <li>Install Yoast SEO</li>
            <li>Go to SEO → General → Configuration Wizard</li>
            <li>Follow the step-by-step setup</li>
            <li>For each page, fill in SEO title, description</li>
        </ol>
    </div>
    
    <footer>
        <p>WordPress Starter Kit | Joala Ventures</p>
    </footer>
</body>
</html>'''
    
    stdin, stdout, stderr = ssh.exec_command('cat > /home/joalacom/public_html/storage/app/public/uploads/products/files/wordpress-starter-kit-free/Setup-Guide.html << \'EOFH\'\n' + setup_guide + '\nEOFH')
    print("Created Setup-Guide.html")
    
    # Create SEO Checklist HTML file  
    seo_checklist = '''<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WordPress SEO Checklist</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, sans-serif; line-height: 1.6; max-width: 800px; margin: 0 auto; padding: 20px; }
        h1 { color: #1a1a1a; border-bottom: 2px solid #3b82f6; padding-bottom: 10px; }
        h2 { color: #374151; margin-top: 30px; }
        .item { background: #f9fafb; border: 1px solid #e5e7eb; padding: 15px; margin: 10px 0; border-radius: 8px; }
        .item h3 { margin: 0 0 10px 0; }
    </style>
</head>
<body>
    <h1>WordPress SEO Checklist</h1>
    <p>Use this checklist to optimize your WordPress site.</p>
    
    <h2>1. Technical SEO</h2>
    <div class="item"><h3>Install Yoast SEO or RankMath</h3></div>
    <div class="item"><h3>Set Up XML Sitemap</h3></div>
    <div class="item"><h3>Enable SSL (HTTPS)</h3></div>
    <div class="item"><h3>Optimize Permalinks - Use "Post name"</h3></div>
    
    <h2>2. On-Page SEO</h2>
    <div class="item"><h3>Write Unique Title Tags (50-60 chars)</h3></div>
    <div class="item"><h3>Add Meta Descriptions (150-160 chars)</h3></div>
    <div class="item"><h3>Use Heading Tags Properly</h3></div>
    <div class="item"><h3>Optimize Images - Add alt text</h3></div>
    
    <h2>3. Content SEO</h2>
    <div class="item"><h3>Write Quality, Helpful Content</h3></div>
    <div class="item"><h3>Use Keywords Naturally</h3></div>
    <div class="item"><h3>Add Internal Links</h3></div>
    
    <h2>4. Speed Optimization</h2>
    <div class="item"><h3>Use Fast Theme (Astra, GeneratePress)</h3></div>
    <div class="item"><h3>Install Caching Plugin</h3></div>
    <div class="item"><h3>Optimize Images</h3></div>
    
    <h2>5. Local SEO</h2>
    <div class="item"><h3>Set Up Google Business Profile</h3></div>
    <div class="item"><h3>Add NAP to Contact Page</h3></div>
    
    <footer><p>WordPress Starter Kit | Joala Ventures</p></footer>
</body>
</html>'''
    
    stdin, stdout, stderr = ssh.exec_command('cat > /home/joalacom/public_html/storage/app/public/uploads/products/files/wordpress-starter-kit-free/SEO-Checklist.html << \'EOFH\'\n' + seo_checklist + '\nEOFH')
    print("Created SEO-Checklist.html")
    
    # Create zip file
    print("\nCreating zip file...")
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/public_html/storage/app/public/uploads/products/files/wordpress-starter-kit-free && zip -r wordpress-starter-kit-free.zip Setup-Guide.html SEO-Checklist.html')
    print(stdout.read().decode())
    print(stderr.read().decode())
    
    # List files
    print("\nListing files:")
    stdin, stdout, stderr = ssh.exec_command('ls -la /home/joalacom/public_html/storage/app/public/uploads/products/files/wordpress-starter-kit-free/')
    print(stdout.read().decode())
    
    # Upload routes
    transport = paramiko.Transport((host, 22))
    transport.connect(username=username, password=password)
    sftp = paramiko.SFTPClient.from_transport(transport)
    sftp.put('routes/web.php', '/home/joalacom/public_html/routes/web.php')
    print("\nUploaded routes/web.php")
    
    # Clear cache
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/public_html && php artisan cache:clear 2>&1')
    print(stdout.read().decode())
    
    sftp.close()
    transport.close()
    ssh.close()
    print("\nDone!")
except Exception as e:
    print(f"Error: {e}")