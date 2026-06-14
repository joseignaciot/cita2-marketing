# Contexto compartido — Agenda de Reservas

> Todos los agentes de `.claude/agents/` DEBEN leer este fichero antes de actuar.
> Es la fuente de verdad sobre negocio, diseño y convenciones técnicas del sitio.

## Negocio

- **Producto:** Agenda de Reservas — SaaS español de gestión de citas y reservas online.
- **Titular:** Tecelia Solutions S.L. — Santiago de Compostela, España. Fundada en 2023.
- **Web:** https://agendadereservas.com
- **Contacto:** hola@agendadereservas.com · soporte@agendadereservas.com (respuesta < 2h en horario laboral, en español)
- **Idioma:** español (es-ES). Moneda: EUR. Mercado: España.
- **Diferenciador clave:** cuota mensual fija **sin comisiones por reserva** (vs TheFork/Booksy que cobran comisión), alojado en la UE y conforme al RGPD, soporte en español.

### Planes
- **Profesional:** 19 €/mes (mayoría de negocios)
- **Restaurante:** 24 €/mes (gestión de mesas y turnos)
- **Turismo:** 29 €/mes (actividades, aforos, guías)
- **Add-on WhatsApp Business API:** +8 €/mes
- **Prueba gratuita:** 14 días sin tarjeta

### Sectores que atendemos
peluquerías y barberías · estética y spa · restaurantes · turismo activo · clínicas de psicología · fisioterapia · veterinarias · dentistas · estudios de tatuaje.

### Competidores (para comparativas)
TheFork/ElTenedor · Booksy · AgendaPro · Bokun · Reservio · Restoo · Treatwell · Fresha · Koibox · Calendly · Acuity · SimplyBook.me · Bookitit.

## Diseño (sistema visual)

- Estilo **inspirado en Apple**: limpio, minimalista, mucho espacio en blanco.
- Tipografía **Inter**. Color primario azul `#0071E3` (clase Tailwind `blue-600`).
- **Tailwind CSS v4**. Bordes redondeados generosos (`rounded-2xl`), sombras suaves en hover.
- Soporte de **modo oscuro** (componente `DarkModeToggle`).

## Convenciones técnicas (Astro SSG)

- **Astro `output: 'static'`** — SSG puro. Deploy = `npm run build` → `dist/` → rsync al VPS (`bash deploy.sh`).
- Páginas en `src/pages/**/*.astro`. Layout en `src/layouts/Layout.astro`.
- **`Layout.astro` props:** `title`, `description`, `aiDescription?`, `ogImage?`, `canonicalUrl?`, `noindex?`, `schema?` (objeto JSON-LD; se fusiona con el `WebSite` por defecto).
- Componentes compartidos: `Header.astro`, `Footer.astro`.
- Verificación GSC y GTM ya integrados en el `<head>` del Layout.
- Ficheros de IA/SEO en `public/`: `robots.txt`, `llms.txt`, `sitemap` (vía `@astrojs/sitemap`).

### Inventario de páginas (junio 2026)
- Home: `index.astro`
- Sectores: `peluquerias`, `estetica`, `spas`, `restaurantes`, `turismo`, `clinicas-psicologia`, `fisioterapia`, `veterinarias`, `dentistas`, `tatuajes`
- Comparativas: `vs/thefork`, `vs/booksy`, `vs/agendapro`, `vs/bokun`, `vs/reservio`, `vs/restoo`
- Guías: `guias/index`, `guias/reducir-ausencias`
- Blog: `blog/index` + 5 artículos + `blog/[slug]` (colección de contenido `posts`)
- Producto/conversión: `precios`, `funcionalidades`, `contacto`
- Legal: `legal/aviso-legal`, `legal/cookies`, `legal/privacidad`; `terminos`, `privacidad` (redirige)

## Principios GEO / SEO para IA (resumen accionable, 2026)

Basado en investigación de junio 2026. Detalle completo en `.claude/seo-geo-estrategia.md`.

1. **Contenido citable > trucos.** Lo que de verdad eleva citaciones (estudio Princeton GEO): **estadísticas con fuente (+31%)**, **citas de expertos/clientes (+41%)**, **enlazar fuentes autoritativas**, **tablas comparativas (2,5× más citas)**, **listicles**. El keyword-stuffing PENALIZA (−10%).
2. **Answer-first.** Cada sección abre con respuesta directa de 1-3 frases bajo un H2/H3 fraseado como la pregunta real del usuario ("¿Cuánto cuesta…?").
3. **Schema JSON-LD** con `@id` enlazado: `Organization` + `SoftwareApplication` (con `offers` EUR + `aggregateRating` REALES) + `FAQPage` + `Service` (por sector) + `BreadcrumbList`. Nunca inventar ratings/precios que no estén visibles en la página.
4. **Crawlers IA:** permitir en `robots.txt` los bots de búsqueda/citación (`OAI-SearchBot`, `Claude-SearchBot`, `PerplexityBot`, `ChatGPT-User`, `Perplexity-User`, `Claude-User`) y de momento también los de entrenamiento. Bloquear `Bytespider`.
5. **Frescura:** fecha visible "Actualizado: …" y refresco de comparativas/precios cada trimestre. Contenido fresco entra en el pool de citación en ~3-5 días.
6. **Indexación:** Google Search Console + **Bing Webmaster Tools + IndexNow** (Bing alimenta a ChatGPT/Copilot).
7. **Fuera del dominio:** reseñas en Capterra/G2/GetApp (≈3× multiplicador de citación para SaaS) y presencia en listicles de terceros.
8. **llms.txt:** mantenerlo (cuesta poco; Claude/Perplexity lo respetan) pero NO es palanca de ranking por sí solo.
