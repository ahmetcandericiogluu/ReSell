# ReSell Design System

Bu dosya, ReSell projesinin design system'ini ve UI component'lerini açıklar.

## Amaç

- **Tutarlılık**: Tüm sayfalarda aynı görünüm ve hissi sağlamak
- **Bakım Kolaylığı**: Tekrar eden kod yerine reusable component'ler
- **Öğrenme**: Gerçek dünya projelerinde kullanılan design system yaklaşımını öğrenmek

## Renk Paleti

### Primary (Mavi)
- Ana marka rengi ve CTA butonları için kullanılır
- `primary-600`: Ana renk (#2563eb)
- `primary-50` - `primary-900`: Açık tonlardan koyu tonlara

### Neutral (Gri)
- Tailwind'in varsayılan `slate` renkleri kullanılır
- Metin, border ve background'lar için

## UI Components

Tüm component'ler `src/components/ui/` klasöründe bulunur.

### Button
**Dosya**: `components/ui/button.jsx`

Variant'lar:
- `primary`: Ana aksiyonlar (Kaydet, Yayınla, vb.)
- `secondary`: İkincil aksiyonlar (İptal, Geri, vb.)
- `ghost`: Minimal görünüm (Navbar linkleri, vb.)
- `danger`: Tehlikeli aksiyonlar (Sil)

Size'lar: `sm`, `md`, `lg`

```jsx
<Button variant="primary" size="md" onClick={handleClick}>
  Kaydet
</Button>
```

### Input & Textarea
**Dosyalar**: `components/ui/input.jsx`, `components/ui/textarea.jsx`

- Standart padding ve border
- Focus state ile ring efekti
- Error state desteği

```jsx
<Input 
  type="email" 
  placeholder="E-posta"
  error={hasError}
/>

<Textarea 
  rows={6}
  placeholder="Açıklama"
/>
```

### Card
**Dosya**: `components/ui/card.jsx`

Variant'lar:
- `default`: Shadow + border
- `bordered`: Sadece border
- `elevated`: Daha belirgin shadow

Padding'ler: `none`, `sm`, `md`, `lg`

```jsx
<Card padding="lg">
  <h2>Başlık</h2>
  <p>İçerik</p>
</Card>
```

### Badge
**Dosya**: `components/ui/badge.jsx`

Status badge'leri için:
- `default`: Gri
- `primary`: Mavi
- `success`: Yeşil (Aktif)
- `warning`: Sarı (Taslak)
- `danger`: Kırmızı (Silindi)

```jsx
<Badge variant="success">Aktif</Badge>
```

### Avatar
**Dosya**: `components/ui/avatar.jsx`

Kullanıcı avatar'ı (initials):

```jsx
<Avatar name="John Doe" size="md" />
```

### FormField
**Dosya**: `components/ui/form-field.jsx`

Label + Input + Error wrapper:

```jsx
<FormField label="E-posta" required error={errors.email}>
  <Input type="email" {...register('email')} />
</FormField>
```

### Container
**Dosya**: `components/ui/container.jsx`

Sayfa layout container'ı:
- `sm`: Formlar için (max-w-2xl)
- `default`: Ana içerik (max-w-7xl)
- `full`: Full width

```jsx
<Container className="py-8">
  {/* Sayfa içeriği */}
</Container>
```

## Component Kullanım İlkeleri

1. **Tailwind utility class'ları component'lere yerleştirilir**
   - ❌ Her sayfada `className="px-4 py-2 bg-primary-600..."` tekrarlamayın
   - ✅ `<Button variant="primary">` kullanın

2. **className prop ile özelleştirme yapılabilir**
   ```jsx
   <Button variant="primary" className="w-full mt-4">
     Tam genişlik buton
   </Button>
   ```

3. **Semantic HTML kullanın**
   - Button'lar `<button>` olmalı
   - Link'ler için react-router `<Link>` kullanın

4. **Accessibility unutmayın**
   - Label'lar inputlarla ilişkilendirilmiş
   - Focus state'ler görünür
   - Disabled state'ler belirgin

## Sayfa Yapısı

Tipik bir sayfa yapısı:

```jsx
<div className="min-h-screen bg-slate-50">
  <Navbar activePage="dashboard" />
  
  <Container className="py-8">
    {/* Sayfa header */}
    <div className="mb-8">
      <h1>Sayfa Başlığı</h1>
      <p>Açıklama</p>
    </div>

    {/* İçerik */}
    <Card>
      ...
    </Card>
  </Container>
</div>
```

## Tailwind Config

`tailwind.config.js` dosyasında:
- Primary renk paleti tanımlı
- Border radius ve shadow scale'leri özelleştirilmiş
- Varsayılan Tailwind utility'leri korunmuş

## Öğrenme Notları

- **Separation of Concerns**: UI logic ve business logic ayrı
- **Reusability**: Aynı component birçok yerde kullanılabilir
- **Consistency**: Design token'lar (renkler, spacing) merkezi yönetiliyor
- **Scalability**: Yeni özellikler eklemek kolay

## Deployment Notları

- TailwindCSS production build'de kullanılmayan class'ları otomatik kaldırır
- Component'ler code-split edilebilir
- Design system değişiklikleri tek noktadan uygulanır

