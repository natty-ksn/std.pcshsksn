# ============================================
# PCSHSKSN e-Portfolio — Smart Deploy Builder
# ============================================
# รันคำสั่ง: .\build_deploy.ps1
# ผลลัพธ์: ไฟล์ deploy_package.zip พร้อมอัปโหลดเซิร์ฟเวอร์

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "  PCSHSKSN e-Portfolio - Deploy Builder" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

$projectDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$outputZip  = Join-Path $projectDir "deploy_package.zip"
$tempDir    = Join-Path $env:TEMP "eportfolio_deploy"

# 1. ลบ temp เก่า (ถ้ามี)
if (Test-Path $tempDir) { Remove-Item $tempDir -Recurse -Force }
if (Test-Path $outputZip) { Remove-Item $outputZip -Force }

# 2. สร้างโฟลเดอร์ temp
New-Item -ItemType Directory -Path $tempDir -Force | Out-Null

# 3. รายการไฟล์/โฟลเดอร์ที่ต้อง "ข้าม" (ไม่นำเข้า Production)
$excludeList = @(
    '.git',
    '.gitignore',
    '.env',
    '.installed',
    '.phpunit.result.cache',
    'node_modules',
    'deploy_package.zip',
    'build_deploy.ps1',
    '.vscode',
    '.idea',
    '*.bak',
    'scripts/maintenance'
)

# รายการไฟล์ใน public/ ที่เป็นไฟล์ debug/fix (ไม่ต้องอัปโหลด)
$excludePublicFiles = @(
    'db_doctor.php',
    'fix_admin.php',
    'fix_passwords.php',
    'fix_settings_encoding.php',
    'reset_test_passwords.php',
    'test.php'
)

Write-Host "[1/5] Scanning project files..." -ForegroundColor Yellow

# 4. คัดลอกไฟล์ — ข้ามรายการที่ไม่ต้องการ
$items = Get-ChildItem -Path $projectDir -Force
foreach ($item in $items) {
    $name = $item.Name
    if ($excludeList -contains $name) {
        Write-Host "  SKIP: $name" -ForegroundColor DarkGray
        continue
    }
    
    $dest = Join-Path $tempDir $name
    if ($item.PSIsContainer) {
        Copy-Item $item.FullName -Destination $dest -Recurse -Force
    } else {
        Copy-Item $item.FullName -Destination $dest -Force
    }
    Write-Host "  COPY: $name" -ForegroundColor Green
}

Write-Host ""
Write-Host "[2/5] Removing debug/fix files from public/..." -ForegroundColor Yellow

# 5. ลบไฟล์ debug ออกจาก public/
foreach ($f in $excludePublicFiles) {
    $fp = Join-Path $tempDir "public\$f"
    if (Test-Path $fp) {
        Remove-Item $fp -Force
        Write-Host "  CLEAN: public/$f" -ForegroundColor DarkGray
    }
}

Write-Host ""
Write-Host "[3/5] Cleaning tmp/ folder..." -ForegroundColor Yellow

# 6. ลบ tmp/ folder content (migrate scripts ฯลฯ)
$tmpFolder = Join-Path $tempDir "tmp"
if (Test-Path $tmpFolder) {
    Remove-Item $tmpFolder -Recurse -Force
    New-Item -ItemType Directory -Path $tmpFolder -Force | Out-Null
    Write-Host "  CLEAN: tmp/ (cleared)" -ForegroundColor DarkGray
}

# 7. ลบ logs content
$logsFolder = Join-Path $tempDir "logs"
if (Test-Path $logsFolder) {
    Remove-Item $logsFolder -Recurse -Force
    New-Item -ItemType Directory -Path $logsFolder -Force | Out-Null
    # สร้าง .gitkeep
    New-Item -ItemType File -Path (Join-Path $logsFolder ".gitkeep") -Force | Out-Null
    Write-Host "  CLEAN: logs/ (cleared)" -ForegroundColor DarkGray
}

# 8. ลบ uploads content (ไม่ส่งรูปเก่าไป production)
$uploadsFolder = Join-Path $tempDir "uploads"
if (Test-Path $uploadsFolder) {
    Remove-Item $uploadsFolder -Recurse -Force
}
# สร้างโครงสร้างโฟลเดอร์เปล่าๆ
@("uploads", "uploads/portfolio", "uploads/portfolio/thumbs", "uploads/avatars") | ForEach-Object {
    $p = Join-Path $tempDir $_
    New-Item -ItemType Directory -Path $p -Force | Out-Null
    New-Item -ItemType File -Path (Join-Path $p ".gitkeep") -Force | Out-Null
}
$sourceUploadHtaccess = Join-Path $projectDir "uploads\.htaccess"
if (Test-Path $sourceUploadHtaccess) {
    Copy-Item $sourceUploadHtaccess (Join-Path $uploadsFolder ".htaccess") -Force
}
Write-Host "  CLEAN: uploads/ (structure + .htaccess security)" -ForegroundColor DarkGray

Write-Host ""
Write-Host "[4/5] Ensuring .env.production exists..." -ForegroundColor Yellow

$envProd = Join-Path $tempDir ".env.production"
if (Test-Path $envProd) {
    Write-Host "  OK: .env.production included" -ForegroundColor Green
} else {
    Write-Host "  WARN: .env.production not found!" -ForegroundColor Red
}

Write-Host ""
Write-Host "[5/5] Creating ZIP package (Linux Compatible)..." -ForegroundColor Yellow

# 9. สร้าง ZIP ด้วย PHP เพื่อความเข้ากันได้กับ Linux (เปลี่ยน \ เป็น /)
# ค้นหา php.exe จาก xampp
$phpExe = "c:\xampp\php\php.exe"
$zipperScript = Join-Path $projectDir "tmp\zipper.php"

if (Test-Path $phpExe) {
    & $phpExe $zipperScript $tempDir
} else {
    Write-Host "  WARN: php.exe not found at $phpExe! Fallback to standard zip..." -ForegroundColor Red
    Compress-Archive -Path "$tempDir\*" -DestinationPath $outputZip -Force
}

# 10. แสดงสรุป
$zipSize = (Get-Item $outputZip).Length
$sizeMB  = [math]::Round($zipSize / 1MB, 2)

Write-Host ""
Write-Host "============================================" -ForegroundColor Green
Write-Host "  BUILD SUCCESS!" -ForegroundColor Green
Write-Host "============================================" -ForegroundColor Green
Write-Host ""
Write-Host "  Output: $outputZip" -ForegroundColor White
Write-Host "  Size:   $sizeMB MB" -ForegroundColor White
Write-Host ""
Write-Host "  Next Steps:" -ForegroundColor Cyan
Write-Host "  1. Upload deploy_package.zip to your server" -ForegroundColor White
Write-Host "  2. Extract to web root (public_html/ or htdocs/)" -ForegroundColor White
Write-Host "  3. Rename .env.production to .env" -ForegroundColor White
Write-Host "  4. Edit .env with your DB credentials" -ForegroundColor White
Write-Host "  5. Open: https://yourdomain.com/install/" -ForegroundColor White
Write-Host ""

# Cleanup temp
Remove-Item $tempDir -Recurse -Force -ErrorAction SilentlyContinue
