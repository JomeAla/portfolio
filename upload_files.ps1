$webclient = New-Object System.Net.WebClient
$webclient.Credentials = New-Object System.Net.NetworkCredential("joalacom", "4fu359TgAMi-O+")

Write-Host "Uploading CustomerController.php..."
try {
    $webclient.UploadFile("ftp://joala.com.ng/public_html/portfolio/app/Http/Controllers/Front/CustomerController.php", "STOR", "C:\Users\jomea\portfolio\app\Http\Controllers\Front\CustomerController.php")
    Write-Host "OK!"
} catch {
    Write-Host "Error: $_"
}

Write-Host "Uploading AutomationEngine.php..."
try {
    $webclient.UploadFile("ftp://joala.com.ng/public_html/portfolio/app/Services/AutomationEngine.php", "STOR", "C:\Users\jomea\portfolio\app\Services\AutomationEngine.php")
    Write-Host "OK!"
} catch {
    Write-Host "Error: $_"
}

Write-Host "Uploading WebhookHub.php..."
try {
    $webclient.UploadFile("ftp://joala.com.ng/public_html/portfolio/app/Services/WebhookHub.php", "STOR", "C:\Users\jomea\portfolio\app\Services\WebhookHub.php")
    Write-Host "OK!"
} catch {
    Write-Host "Error: $_"
}

Write-Host "Done!"