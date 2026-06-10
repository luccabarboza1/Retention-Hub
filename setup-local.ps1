Write-Host "== Retention Hub -- Setup Local ==" -ForegroundColor Cyan

Set-Location C:\dev\ombudsman

Write-Host "`n[1/3] Subindo banco Postgres..." -ForegroundColor Yellow
docker compose up -d
if ($LASTEXITCODE -ne 0) { Write-Host "Erro ao subir Docker." -ForegroundColor Red; exit 1 }

Write-Host "`n[2/3] Aguardando Postgres ficar pronto..." -ForegroundColor Yellow
Start-Sleep -Seconds 3

Write-Host "`n[3/3] Rodando migrations e seed..." -ForegroundColor Yellow
npx prisma migrate dev --name init
if ($LASTEXITCODE -ne 0) { Write-Host "Erro na migration." -ForegroundColor Red; exit 1 }
npx prisma db seed
if ($LASTEXITCODE -ne 0) { Write-Host "Erro no seed." -ForegroundColor Red; exit 1 }

Write-Host "`n== Pronto! Iniciando servidor de desenvolvimento... ==" -ForegroundColor Green
npm run dev
