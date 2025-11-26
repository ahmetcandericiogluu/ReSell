# Production Session Fix

## Sorun
Production'da (Render.com) sayfayı yenilediğinde session kayboluyordu.

## Çözüm

### 1. Session Cookie Ayarları (`backend/config/packages/framework.yaml`)

```yaml
session:
    cookie_samesite: none  # Cross-site istekler için gerekli
    cookie_secure: true    # HTTPS için zorunlu (prod)
    name: RESELL_SESSION   # Özel session ismi
```

**Neden?** Frontend ve backend farklı subdomain'lerde olduğu için SameSite=None gerekli.

### 2. CORS Ayarları (`backend/config/packages/nelmio_cors.yaml`)

```yaml
allow_headers: ['Content-Type', 'Authorization', 'Cookie']
expose_headers: ['Link', 'Set-Cookie']
allow_credentials: true
```

**Neden?** Cross-origin cookie paylaşımı için.

### 3. Environment Variables (Render Dashboard)

**Backend Service:**
```
CORS_ALLOW_ORIGIN=^https://.*\.onrender\.com$
```

Regex pattern kullanarak tüm onrender subdomain'lerine izin veriyoruz.

## Deployment Sonrası Test

1. Render Dashboard'a gidin
2. Backend service → Environment → CORS_ALLOW_ORIGIN kontrol edin
3. Deploy sonrası test edin:
   - Giriş yapın
   - Sayfayı yenileyin (F5)
   - Session korunmalı

## Alternatif: Tam Domain Kullanımı

Eğer regex çalışmazsa tam domain kullanın:

```
CORS_ALLOW_ORIGIN=https://resell-frontend.onrender.com
```

## Debugging

### Browser Console'da Cookie Kontrolü

```javascript
// Application tab → Cookies kontrol edin
// RESELL_SESSION cookie'si olmalı
// Attributes:
// - Secure: ✓
// - HttpOnly: ✓
// - SameSite: None
```

### Backend Logs

```bash
# Render Dashboard → Logs
# Session başlatma mesajlarını arayın
```

## Notlar

- Local development'ta `SameSite=lax` yeterli
- Production'da `SameSite=none` + `Secure=true` şart
- Cookie header'ları CORS'ta açıkça belirtilmeli

