param (
    [string]$Tag = "dev"
)

$ErrorActionPreference = "Stop"

$BinDir = $PSScriptRoot
$DistDir = Join-Path $PSScriptRoot "..\dist"

if (Test-Path $DistDir) {
    Remove-Item -Recurse -Force $DistDir
}
New-Item -ItemType Directory -Path $DistDir | Out-Null

$Targets = @(
    @{ OS = "linux";   Arch = "amd64"; Binary = "invoice-pdf";     Extension = "tar.gz" },
    @{ OS = "linux";   Arch = "arm64"; Binary = "invoice-pdf";     Extension = "tar.gz" },
    @{ OS = "darwin";  Arch = "amd64"; Binary = "invoice-pdf";     Extension = "tar.gz" },
    @{ OS = "darwin";  Arch = "arm64"; Binary = "invoice-pdf";     Extension = "tar.gz" },
    @{ OS = "windows"; Arch = "amd64"; Binary = "invoice-pdf.exe"; Extension = "zip" },
    @{ OS = "windows"; Arch = "arm64"; Binary = "invoice-pdf.exe"; Extension = "zip" }
)

try {
    foreach ($target in $Targets) {
        $archive = Join-Path $DistDir ("invoice-pdf_{0}_{1}.{2}" -f $target.OS, $target.Arch, $target.Extension)
        $buildDir = Join-Path $DistDir ("build_{0}_{1}" -f $target.OS, $target.Arch)
        $output = Join-Path $buildDir $target.Binary

        Write-Host ("--> Building {0}/{1}" -f $target.OS, $target.Arch) -ForegroundColor Yellow
        New-Item -ItemType Directory -Path $buildDir | Out-Null

        $env:GOOS = $target.OS
        $env:GOARCH = $target.Arch
        $env:CGO_ENABLED = "0"

        Push-Location $BinDir
        try {
            go build -trimpath -ldflags="-s -w -X main.Version=$Tag" -o $output .
            if ($LASTEXITCODE -ne 0) { throw "go build failed for $($target.OS)/$($target.Arch)" }
        }
        finally {
            Pop-Location
        }

        if ($target.Extension -eq "zip") {
            Compress-Archive -Path $output -DestinationPath $archive -Force
        }
        else {
            tar -czf $archive -C $buildDir $target.Binary
        }

        Remove-Item -Recurse -Force $buildDir
    }
}
finally {
    Remove-Item Env:\GOOS, Env:\GOARCH, Env:\CGO_ENABLED -ErrorAction SilentlyContinue
}

Get-ChildItem $DistDir | Select-Object Name, Length | Format-Table -AutoSize
Write-Host "Upload with: gh release upload $Tag dist/*" -ForegroundColor Gray
