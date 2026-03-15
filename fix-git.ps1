$lockFile = "C:\Users\KOEMSEANG\PHOEURN\Desktop\Rental_Service\.git\index.lock"
if (Test-Path $lockFile) {
    Remove-Item $lockFile -Force
    Write-Host "Lock file removed"
} else {
    Write-Host "No lock file found"
}

Set-Location "C:\Users\KOEMSEANG\PHOEURN\Desktop\Rental_Service"
git pull origin feature/test

