# ReSell - Tüm Servisleri Başlat
Write-Host "🚀 ReSell Servisleri Başlatılıyor..." -ForegroundColor Cyan

# Docker Compose ile tüm servisleri başlat
docker-compose up -d --build

Write-Host ""
Write-Host "✅ Tüm servisler başlatıldı!" -ForegroundColor Green
Write-Host ""
Write-Host "📍 Servis URL'leri:" -ForegroundColor Yellow
Write-Host "   Frontend:        http://localhost:3000"
Write-Host "   Backend:         http://localhost:8000"
Write-Host "   Auth Service:    http://localhost:8001"
Write-Host "   Listing Service: http://localhost:8082"
Write-Host ""
Write-Host "📊 Logları görmek için: docker-compose logs -f"
Write-Host "🛑 Durdurmak için:      docker-compose down"

