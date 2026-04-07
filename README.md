# Cita2 Marketing Site

Sitio web de marketing para Cita2 - Software de gestión de citas y reservas.

## 🚀 Tecnología

- **Astro** - Framework estático para máximo rendimiento SEO
- **Tailwind CSS** - Estilos utilitarios
- **TypeScript** - Tipado estático
- **Schema.org** - Rich snippets para SEO
- **Content Collections** - CMS basado en Markdown

## 📁 Estructura

```
/
├── src/
│   ├── components/     # Componentes reutilizables
│   │   ├── Header.astro, Hero.astro, Features.astro
│   │   ├── Testimonials.astro, Pricing.astro, FAQ.astro
│   │   ├── CTA.astro, Footer.astro
│   ├── content/        # CMS - Contenido gestionable
│   │   ├── landings/   # Landing pages (markdown)
│   │   ├── faqs/       # Preguntas frecuentes
│   │   ├── testimonials/ # Testimonios clientes
│   │   └── features/   # Funcionalidades
│   ├── layouts/        # Layouts de página
│   │   └── Layout.astro (SEO + Analytics)
│   ├── pages/          # Páginas (routing automático)
│   │   ├── index.astro
│   │   └── peluquerias.astro
│   └── styles/         # Estilos globales
├── public/             # Assets estáticos
└── astro.config.mjs
```

## 📊 Analytics Configurado

### Google Analytics 4

```bash
# Copiar .env.example a .env
cp .env.example .env

# Añadir tu GA ID
PUBLIC_GA_ID=G-XXXXXXXXXX
```

### Plausible (Alternativa privacy-friendly)

```bash
# Añadir dominio
PUBLIC_PLAUSIBLE_DOMAIN=app.agendadereservas.com
```

Características:
- ✅ Partytown para lazy loading (no bloquea render)
- ✅ Anonymize IP para GDPR
- ✅ No personal ads signals
- ✅ Condicional (solo carga si hay ID configurado)

## 📝 CMS - Gestionar Landings sin Código

### Estructura Content Collections

Las landings se definen en `src/content/` como archivos **Markdown** con frontmatter:

```
src/content/
├── landings/
│   └── peluquerias.md      # Landing page completa
├── faqs/
│   ├── peluquerias-1.md    # FAQ específica
│   └── peluquerias-2.md
├── testimonials/
│   └── maria-garcia.md     # Testimonio cliente
└── features/
    └── reservas-online.md  # Funcionalidad
```

### Crear una Landing Nueva

**Método 1: Markdown (Recomendado para equipos no técnicos)**

1. Crear archivo en `src/content/landings/[nombre].md`:

```markdown
---
title: "Software para Restaurantes - Cita2"
description: "Sistema de reservas sin comisiones..."
sector: "restaurantes"
keywords: ["software restaurante", "reservas restaurante"]

heroTitle: "Sistema de reservas que llena tu restaurante"
heroSubtitle: "Olvídate de pagar comisiones a ElTenedor..."
heroCta: "Prueba gratis 30 días"

featuresTitle: "¿Qué conseguirás con Cita2?"
testimonialsTitle: "Lo que dicen los restaurantes"
showPricing: true

faqTitle: "Preguntas frecuentes"
ctaTitle: "Empieza a llenar tu restaurante"
ctaSubtitle: "Únete a 100+ restaurantes que ya ahorran..."
---

Contenido opcional en markdown...
```

2. Crear FAQs en `src/content/faqs/restaurantes-1.md`:

```markdown
---
question: "¿Funciona con mi TPV?"
answer: "Sí, integramos con los principales TPVs del mercado..."
sector: "restaurantes"
order: 1
---
```

3. Crear testimonios en `src/content/testimonials/`:

```markdown
---
quote: "Ahorramos 2.400€ al mes en comisiones"
author: "Carlos Martínez"
role: "Gerente"
company: "Restaurante La Trastienda"
rating: 5
sector: "restaurantes"
featured: true
---
```

4. Rebuild automático: `npm run build`

**Método 2: Astro Template (Para desarrolladores)**

Crear `src/pages/restaurantes.astro` importando componentes (como `peluquerias.astro`).

### Gestión de Contenido

| Tipo | Ubicación | Ejemplo |
|------|-----------|---------|
| **Landing** | `src/content/landings/` | `peluquerias.md` |
| **FAQ** | `src/content/faqs/` | `peluquerias-1.md` |
| **Testimonio** | `src/content/testimonials/` | `maria-garcia.md` |
| **Feature** | `src/content/features/` | `reservas-online.md` |

## 🛠️ Desarrollo

```bash
# Instalar dependencias
npm install

# Configurar analytics
cp .env.example .env
# Editar .env con tus IDs

# Servidor de desarrollo
npm run dev

# Build para producción
npm run build

# Preview del build
npm run preview
```

## 📄 Páginas incluidas

- **Home** (`/`) - Página principal con todas las funcionalidades
- **Peluquerías** (`/peluquerias/`) - Landing específica sector peluquería

## 🎯 SEO Features

- ✅ Schema.org (SoftwareApplication, FAQPage, WebSite)
- ✅ Meta tags Open Graph
- ✅ Twitter Cards
- ✅ Sitemap XML (automático con @astrojs/sitemap)
- ✅ URLs canónicas
- ✅ Lazy loading de analytics con Partytown

## 🚢 Deploy

### Static hosting (Netlify, Vercel, Cloudflare Pages)

```bash
npm run build
# Subir carpeta 'dist/'
```

### Subdominio de app.agendadereservas.com

```bash
# Build con ruta base
npm run build
# Copiar 'dist/' a servidor app.agendadereservas.com/lp/ o similar
```

## 🎨 Branding

- **Primary color:** `#2563eb` (blue-600)
- **Success:** `#10b981` (emerald-500)
- **Warning:** `#f59e0b` (amber-500)
- **Font:** Inter (Google Fonts)

## 📚 Documentación relacionada

- `/docs/SEO_STRATEGY.md` - Estrategia SEO completa
- `/docs/SEO_PAGE_BRIEFS.md` - Briefs por landing page
- `/docs/SEO_LANDING_TEMPLATE.md` - Template HTML
- `/docs/MARKETING_COPY_EXAMPLES.md` - Copy de ejemplo

---

**URL:** https://app.agendadereservas.com
