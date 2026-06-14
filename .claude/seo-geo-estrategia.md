# Estrategia SEO + GEO + Red de páginas — Agenda de Reservas

Plan vivo. Basado en investigación de junio 2026 (GEO, schema, crawlers IA, patrones de contenido, directorios). Lo ejecutan los agentes de `.claude/agents/`.

---

## Decisiones de arquitectura (FIJADAS — no reabrir sin motivo)

- **Un solo dominio, subdirectorios.** Todo en `agendadereservas.com` (`/guias`, `/comparativas` (=`/vs`), sectores…). NADA de dominios separados ni subdominios: la autoridad topical se acumula en un dominio y las páginas solo "se potencian entre sí" vía enlazado interno dentro del mismo sitio. Varios dominios = enlaces entre sitios propios (esquema de enlaces, penalizable) + cada dominio parte de cero.
- **Sin dominios exact-match ni satélites.** Los EMD ya no dan ventaja (EMD update). No comprar dominios extra de momento.
- **Hosting = el actual, ya óptimo.** Astro SSG → HTML estático → lo más rápido y accesible que existe. Servido desde el VPS; recomendado poner **Cloudflare gratis** delante (CDN + caché + SSL). No usar hosting "gratuito" tipo `*.netlify.app`/`*.vercel.app` (posicionan mal, no se controlan). Cero infra nueva.
- **Gestión:** todo en este repo Astro; cada página es un `.astro`; los agentes las generan; git versiona; `deploy.sh` publica.

---

## Pilar 0 — Estudio de palabras (cómo, sin presupuesto)

El método, con herramientas gratis, en orden de valor:

1. **Google Search Console (ya verificado)** — LA fuente principal. Da las *queries reales* por las que el sitio ya aparece (impresiones, posición, CTR). De ahí salen: páginas que faltan, páginas a mejorar, y keywords con impresiones pero mala posición (fruta madura). Ninguna herramienta de pago iguala esto para *este* sitio.
2. **Google Keyword Planner** (gratis con cuenta Ads) — volúmenes orientativos.
3. **Autocompletar + "Otras preguntas (PAA)" + búsquedas relacionadas** — material para H2 en forma de pregunta y FAQ.
4. **Google Trends** — estacionalidad por sector.
5. **Competidores** (Booksy, AgendaPro, Koibox, TheFork…) — qué cubren que nosotros no.
6. **Prompts reales a IA** — preguntas que la gente hace a ChatGPT/Perplexity, para GEO.

Flujo: *semilla (sector × intención) → expandir con GSC + autocomplete + competidores → agrupar en clusters → 1 keyword primaria por página → priorizar por intención y "ganabilidad" (long-tail en español = victorias rápidas)*. Lo ejecuta `estratega-keywords`, coordinado con `arquitecto-red-paginas`.

---

## Pilar 1 — Red de páginas especializadas (topical authority)

Objetivo: que sectores, comparativas y guías formen **clusters hub-and-spoke** que se enlacen entre sí, de modo que cada página transfiera autoridad a las demás y el sitio sea reconocido como la fuente experta del nicho (clave tanto para Google como para citación por IA).

### Arquitectura hub-and-spoke

```
HUB (pillar)            SPOKES (cluster)
/peluquerias    ──┬──>  /guias/reducir-ausencias-peluqueria
                  ├──>  /vs/booksy   (Booksy es competidor de peluquería)
                  ├──>  /blog/mejor-software-peluquerias-2026
                  └──>  /guias/digitalizar-agenda-barberia
        ^  ^  ^
        └──┴──┴── cada spoke enlaza de vuelta al hub + a 2-3 spokes hermanos
```

- **Hubs = páginas de sector** (peluquerías, estética, restaurantes, turismo…). Son el pilar de cada cluster.
- **Spokes = guías, comparativas, how-to, FAQ** específicos de ese sector.
- Cada comparativa `/vs/*` enlaza a los sectores donde ese competidor es relevante.
- Las guías enlazan al sector pilar + a la comparativa relevante + al blog.

### Reglas de enlazado interno (las aplica `arquitecto-red-paginas`)
1. Cada página nueva enlaza a: **su hub** + **2-3 páginas hermanas** + **1 comparativa** + **1 guía/blog** relacionada.
2. **Anchor text descriptivo** (no "haz clic aquí"): usa la keyword objetivo del destino.
3. **Un keyword primario por página** — el `estratega-keywords` verifica que no haya canibalización.
4. Cada hub muestra un bloque "Recursos para [sector]" linkando a sus spokes (con schema `ItemList`).
5. `BreadcrumbList` en todas las páginas internas; breadcrumb visible coherente con la jerarquía.

### Backlog de clusters a construir (prioridad)
- **Peluquerías/barberías**: hub existe → faltan guías (digitalizar agenda, reducir no-shows en peluquería) y FAQ.
- **Estética/spa**: hub existe → guía "software cabinas/tratamientos", comparativa Treatwell/Fresha.
- **Restaurantes**: hub + /vs/thefork + /vs/restoo → faltan guías (gestión de turnos, depósitos) y FAQ.
- **Turismo activo**: hub + /vs/bokun → guía "aforos y comisiones", comparativa adicional.
- **Verticales con hub pero sin cluster**: dentistas, fisioterapia, veterinarias, psicología, tatuajes → añadir 1 guía + FAQ por vertical cuando haya tráfico que lo justifique.
- **Comparativas pendientes**: /vs/treatwell, /vs/fresha, /vs/koibox, /vs/calendly, /vs/simplybook, /vs/acuity.

---

## Pilar 2 — GEO / SEO para IA (cambios en el sitio)

Checklist accionable (las ejecuta `optimizador-geo` + `auditor-seo-tecnico`):

- [ ] **robots.txt**: añadir bloques explícitos para bots IA de búsqueda/citación (allow) y `Bytespider` (block). Plantilla en el agente `optimizador-geo`.
- [ ] **Schema stack** con `@id` enlazado en home y páginas clave: `Organization` (+`sameAs` a redes + Wikidata cuando exista), `SoftwareApplication` (+`offers` EUR +`aggregateRating` reales), `FAQPage`, `Service` por sector, `BreadcrumbList`.
- [ ] **Patrones citables** en cada página: TL;DR/answer-first, H2 en forma de pregunta, tablas comparativas, estadísticas con fuente, citas de clientes reales, fecha "Actualizado".
- [ ] **llms.txt**: mantener actualizado; opcional `llms-full.txt`.
- [ ] **Tracking GA4**: canal personalizado "AI Referrals" (regex de `chatgpt.com|perplexity.ai|claude.ai|gemini.google.com|copilot.microsoft.com|...`) por encima de Referral.
- [ ] **Indexación**: alta en **Bing Webmaster Tools** + **IndexNow** (clave para ChatGPT/Copilot); sitemap en GSC con `lastmod` correcto.
- [ ] **Monitorización**: set de prompts en español (p.ej. "mejor software de reservas para peluquerías", "alternativa a Booksy") revisado semanal; herramienta opcional (Otterly.ai ~29€ o GEO Metrics, nativa ES).

---

## Pilar 3 — Distribución / directorios (lo prepara `gestor-directorios`)

**Tier 1 (gratis, primero):** Google Business Profile · Capterra.es · GetApp.es · G2 · Software Advice · Trustpilot · AlternativeTo · SaaSHub · Crunchbase · SoftDoit.es.
**Tier 2 (ES + UE):** Appvizer.es · Páginas Amarillas · QDQ · Cylex · Europages · European-Alternatives.eu · Startupxplore · SourceForge · TrustRadius.
**Tier 3 (lanzamiento/comunidad):** Product Hunt (coordinado) · BetaList · Indie Hackers · pitch a El Referente / Todostartups · inclusión en listicles ES.
**Solo si hay feature de IA real:** TAAFT · Futurepedia · Toolify (si no, se saltan — rechazan tools no-IA).

Nota: muchos directorios ES son nofollow (siguen aportando NAP/citación/datos para IA). Dofollow fuerte: Capterra/G2/GetApp/AlternativeTo/SaaSHub/Product Hunt/European-Alternatives.

---

## Pilar 4 — Automatización

- Ejecutar `auditor-seo-tecnico` + `optimizador-geo` en cadencia (skill `/loop` o hook semanal).
- `arquitecto-red-paginas` propone el siguiente cluster/página cada semana; `redactor-seo-es` la redacta; `auditor-seo-tecnico` valida antes de commit.

---

## Los agentes (`.claude/agents/`)

| Agente | Modelo | Rol |
|---|---|---|
| `arquitecto-red-paginas` | sonnet | Diseña clusters, decide páginas nuevas, define el enlazado interno y la jerarquía. |
| `estratega-keywords` | sonnet | Keywords, gaps, ideas de /vs y sectoriales, evita canibalización. |
| `redactor-seo-es` | sonnet | Redacta páginas Astro en la voz del sitio con patrones citables (ES). |
| `auditor-seo-tecnico` | sonnet | Auditoría técnica: schema, meta, canonical, sitemap, enlaces, CWV. |
| `optimizador-geo` | sonnet | GEO/IA: llms.txt, schema stacking, robots IA, tracking, patrones de citación. |
| `gestor-directorios` | haiku | Prepara fichas/copys de alta para cada directorio. |
