# Contexto compartido — Agenda de Reservas

> Todos los agentes de `.claude/agents/` DEBEN leer este fichero antes de actuar.
> Es la fuente de verdad sobre negocio, diseño y convenciones técnicas del sitio.

## Negocio

- **Producto:** Agenda de Reservas — SaaS español de gestión de citas y reservas online.
- **Titular:** Tecelia Solutions S.L. — Santiago de Compostela, España. Fundada en 2023.
- **Web:** https://agendadereservas.com
- **Contacto:** hola@agendadereservas.com · soporte@agendadereservas.com (respuesta < 2h en horario laboral, en español)
- **Idioma:** español (es-ES). Mercado objetivo del sitio: España. **Cuota de suscripción en EUR** (ver `pricing.json`).
- **Producto multi-moneda / multi-país.** El software opera con cualquier moneda y pasarela según el cliente (p. ej. clientes en Chile usan **CLP** y **WEBPAY**; en España, **EUR** y **Stripe**). ⚠️ Por eso NUNCA afirmar que "solo funciona en EUR" o "solo en España": la cuota de AdR está en EUR para el mercado español, pero el motor de reservas y los cobros soportan otras monedas. El argumento "factura desde España con IVA" es correcto porque se refiere a cómo **Tecelia S.L. factura su suscripción** al negocio español.
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

## Funcionalidades REALES del producto (verificado por capturas del panel, junio 2026)

> Fuente de verdad para describir el producto. NO inventar funciones que no estén aquí. Si falta algo, marcarlo como "por confirmar".

**Navegación del panel:** Panel (dashboard) · Calendario · Todas las Reservas · Clientes (CRM) · Estadísticas · Configuración · **Motor de Reservas** (página pública de reserva) · **Asistente IA** (botón flotante) · modo claro/oscuro · multi-idioma de interfaz.

**Dashboard / Panel:** Reservas de hoy · Ingresos estimados del día · **Ocupación de plazas** (p. ej. 2/40) · Próxima reserva · Citas de la semana con gráfico y % vs semana anterior · Servicios/actividades más solicitados · Nuevos clientes · Citas de hoy · Últimas reservas · vista agenda Día/Mañana/Semana · **Recordatorios enviados**.

**Ficha de reserva / cita:** referencia (#ID) · estados (Confirmada, etc.) · **Check-in** / **No Show** · contacto del cliente (WhatsApp, email) · **Resumen de pagos: Total / Pagado / Pendiente** (pagos parciales y depósitos reales) · historial de pagos con pasarela (WEBPAY/Stripe), orden, auth, tarjeta · "Registrar pago" · actividad/servicio contratado · guía asignado · nº de tickets · hora · punto de encuentro · datos adicionales (país, dirección de recogida) · origen (Web).

**Comunicación (Communication Hub):** **WhatsApp Business API** · **SMS (Twilio)** · Email · **Asistente de Voz** · **Chat Widget** · **Encuestas** de satisfacción · **Notificaciones Push** · plantillas con variables (`{{firstName}}`, `{{activityName}}`, `{{totalPrice}}`, etc.) · log de envíos con estado de entrega (success) y filtro Email/WhatsApp · **Programador de Informes**.

**Configuración › Negocio:** datos del negocio · Horario · **Puestos de trabajo** (recursos/profesionales) · Días cerrados · **Política de Cancelación** · Enlace de reservas · **Facturación**.

**Configuración › Actividades turísticas / servicios:** Actividades · **Reglas de precio** · **Extras** · **Campos extra** (formulario personalizado) · **Idiomas** · **Pagos Online**.

**Motor de reservas público (lo que ve el cliente):** asistente de **5 pasos** (Actividad → Fecha y hora → Extras → Tus datos → Confirmación) · selector de idioma · precio por composición del grupo (adultos/niños/bebés/seniors) · selección de fecha con calendario · totales en vivo · marca del negocio.

**Verticales soportadas a nivel de datos:** citas de servicios (peluquería, salud) con profesional/puesto y duración; y actividades turísticas con aforo, guía, idiomas, meeting point, qué incluye/no incluye/qué llevar, recogida.

## Diseño (sistema visual)

- Estilo **inspirado en Apple**: limpio, minimalista, mucho espacio en blanco.
- Tipografía **Inter**. Color primario azul `#0071E3` (clase Tailwind `blue-600`).
- **Tailwind CSS v4**. Bordes redondeados generosos (`rounded-2xl`), sombras suaves en hover.
- Soporte de **modo oscuro** (componente `DarkModeToggle`).

### Lenguaje visual del PANEL real (para mockups fieles al producto)
> Verificado por capturas del panel (junio 2026). Usar esto al recrear la UI en HTML/CSS con datos ficticios. NUNCA publicar datos reales de clientes.
- **Layout:** sidebar izquierdo blanco con logo "Agenda" (icono edificio azul) + ítems de navegación con icono; botón **"Nueva reserva"** azul sólido arriba; contenido a la derecha sobre fondo gris muy claro (`gray-50`).
- **Tarjetas KPI** arriba: blancas, `rounded-2xl`, borde sutil `gray-200`, icono + etiqueta gris + cifra grande. Ingresos en **verde**; alertas/pendiente en **rojo/ámbar**.
- **Badges de estado:** píldoras suaves — verde (`Confirmada`/`success`), tonos suaves para otros estados.
- **Botón Asistente IA** flotante abajo-derecha con degradado azul→violeta.
- **Acciones de cita:** botón **Check-in** verde, **No Show** rojo.
- **Tablas/listas:** filas con `divide-y`, hover gris, iconos de canal (Email morado, WhatsApp verde).
- **Wizard público:** pasos numerados en círculo (verde ✓ completado, azul el actual), contadores +/- para personas, chips de fecha.

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
