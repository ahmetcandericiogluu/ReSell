# Environment Variables Template

## Required Environment Variables

Add these to your `.env` or `.env.local` file for local development, or to your hosting platform's environment variables for production.

### R2 / S3 Storage Configuration

```bash
###> R2 / S3 STORAGE ###
# Cloudflare R2 Configuration
# For local development, leave these empty to use local storage
# For production with R2, fill in your R2 credentials

R2_ENDPOINT=
R2_REGION=auto
R2_BUCKET=
R2_ACCESS_KEY_ID=
R2_SECRET_ACCESS_KEY=
R2_PUBLIC_BASE_URL=
###< R2 / S3 STORAGE ###
```

## Example R2 Values (Production)

```bash
R2_ENDPOINT=https://your-account-id.r2.cloudflarestorage.com
R2_REGION=auto
R2_BUCKET=resell-images
R2_ACCESS_KEY_ID=your-r2-access-key-id
R2_SECRET_ACCESS_KEY=your-r2-secret-access-key
R2_PUBLIC_BASE_URL=https://images.yourdomain.com
```

## Switching Storage Backend

### Using Local Storage (Default)

In `config/services.yaml`:

```yaml
App\Storage\StorageInterface:
    alias: App\Storage\LocalStorageService

App\Service\ListingImageService:
    arguments:
        $storageDriver: 'local'
```

### Using Cloudflare R2 Storage

1. Set the environment variables above
2. In `config/services.yaml`, change:

```yaml
App\Storage\StorageInterface:
    alias: App\Storage\R2StorageService

App\Service\ListingImageService:
    arguments:
        $storageDriver: 'r2'
```

## Render Deploy Instructions

When deploying to Render, add these environment variables in your service settings:

1. Go to your service dashboard
2. Navigate to "Environment" tab
3. Add the following environment variables:
   - `R2_ENDPOINT`
   - `R2_REGION` (set to `auto`)
   - `R2_BUCKET`
   - `R2_ACCESS_KEY_ID`
   - `R2_SECRET_ACCESS_KEY`
   - `R2_PUBLIC_BASE_URL`

4. Update `config/services.yaml` to use R2StorageService (as shown above)
5. Deploy your changes

## Local Development

For local development, you don't need to set R2 environment variables. The system will use local storage by default, storing files in `public/uploads/`.

Make sure the directory is writable:
```bash
mkdir -p public/uploads
chmod 755 public/uploads
```

