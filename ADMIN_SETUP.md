# Decap CMS Setup

Este archivo configura Decap CMS para gestión de contenido visual.

## URL del Admin

https://app.agendadereservas.com/admin/

## Autenticación

Decap CMS usa **Git Gateway** para autenticación. Necesitas:

1. **Netlify Identity** (recomendado) o
2. **GitHub/GitLab OAuth** con backend propio

### Opción 1: Netlify Identity (Más fácil)

1. Desplegar sitio en Netlify
2. Activar Identity: Site settings → Identity → Enable
3. Invitar usuarios al equipo
4. Configurar Git Gateway: Services → Git Gateway → Enable
5. Listo - el login aparece automáticamente en /admin/

### Opción 2: GitHub OAuth (Self-hosted)

Si usas servidor propio, necesitas un backend OAuth:

```javascript
// server.js ejemplo con express
const express = require('express');
const app = express();

// Usar un backend OAuth como:
// - netlify-cms-oauth-provider
// - decap-server
// - custom OAuth

app.listen(3000);
```

## Colecciones Disponibles

| Colección | Descripción | Campos principales |
|-----------|-------------|-------------------|
| **Landing Pages** | Páginas de marketing | Título, Hero, Features, Pricing, FAQ, CTA |
| **FAQs** | Preguntas frecuentes | Pregunta, Respuesta, Sector |
| **Testimonios** | Casos de éxito | Cita, Autor, Empresa, Valoración |
| **Funcionalidades** | Features del producto | Título, Descripción, Icono |

## Flujo de Trabajo

1. Ir a https://app.agendadereservas.com/admin/
2. Hacer login con credenciales
3. Seleccionar colección (ej: Landing Pages)
4. Crear/Editar contenido
5. Guardar → Commit automático a Git
6. CI/CD rebuild y deploy automático

## Configuración de Campos

Ver `public/admin/config.yml` para configuración completa.

### Landing Page Template

```yaml
title: "Título SEO"
description: "Meta description"
sector: "peluquerias"
heroTitle: "Título principal"
heroSubtitle: "Subtítulo descriptivo"
heroCta: "Texto del botón"
featuresTitle: "Título sección features"
testimonialsTitle: "Título testimonios"
showPricing: true
faqTitle: "Preguntas frecuentes"
ctaTitle: "Título CTA final"
```

## Customización

### Estilos del Editor

El archivo `admin/index.html` carga Tailwind CSS para previsualización básica.

Para customizar más:
1. Crear archivo CSS personalizado
2. Registrar en `CMS.registerPreviewStyle()`

### Widgets Personalizados

```javascript
CMS.registerEditorComponent({
  id: 'youtube',
  label: 'YouTube',
  fields: [{name: 'id', label: 'Video ID', widget: 'string'}],
  pattern: /^<iframe.*youtube.com\/embed\/(\S+).*><\/iframe>$/,
  fromBlock: function(match) {
    return {id: match[1]};
  },
  toBlock: function(obj) {
    return '<iframe src="https://www.youtube.com/embed/' + obj.id + '"></iframe>';
  }
});
```

## Troubleshooting

### "Failed to load config.yml"
- Verificar que el archivo exista en `/admin/config.yml`
- Verificar sintaxis YAML (sin tabs, solo espacios)

### "Login no funciona"
- Netlify Identity debe estar habilitado
- Git Gateway debe estar configurado
- Usuario debe estar invitado al equipo

### "No se ven cambios"
- Revisar que el build se ejecute después del commit
- Verificar que `branch: main` coincida con tu repo

## Documentación

- [Decap CMS Docs](https://decapcms.org/docs/)
- [Git Gateway](https://decapcms.org/docs/git-gateway-backend/)
- [Widgets](https://decapcms.org/docs/widgets/)

---

**Nota:** Decap CMS guarda cambios directamente en Git. Asegúrate de tener CI/CD configurado para rebuild automático.
