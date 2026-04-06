import { defineCollection, z } from 'astro:content';
import { glob } from 'astro/loaders';

const landings = defineCollection({
  loader: glob({ pattern: '**/*.md', base: './src/content/landings' }),
  schema: z.object({
    // SEO
    title: z.string(),
    description: z.string(),
    ogImage: z.string().optional(),
    noindex: z.boolean().default(false),
    
    // Hero Section
    heroTitle: z.string(),
    heroSubtitle: z.string(),
    heroCta: z.string().default('Prueba gratis 14 días'),
    heroCtaHref: z.string().default('https://agendadereservas.com/register'),
    heroSecondaryCta: z.object({
      text: z.string(),
      href: z.string(),
    }).optional(),
    
    // Features Section
    featuresTitle: z.string().default('¿Qué conseguirás con Agenda de Reservas?'),
    featuresSubtitle: z.string().default('Funcionalidades diseñadas específicamente para tu sector.'),
    
    // Testimonials
    testimonialsTitle: z.string().default('Lo que dicen nuestros clientes'),
    testimonialsSubtitle: z.string().optional(),
    
    // Pricing
    showPricing: z.boolean().default(true),
    
    // FAQ
    faqTitle: z.string().default('Preguntas frecuentes'),
    faqSubtitle: z.string().default('Respuestas a las dudas más comunes.'),
    
    // CTA Final
    ctaTitle: z.string().default('¿Listo para simplificar tu gestión de citas?'),
    ctaSubtitle: z.string().default('Únete a 500+ negocios que ya ahorran tiempo con Agenda de Reservas.'),
    
    // Comparison table
    showComparison: z.boolean().default(true),
    
    // Schema.org
    schemaType: z.enum(['SoftwareApplication', 'Service', 'Product']).default('SoftwareApplication'),
    
    // Metadata
    sector: z.string(),
    keywords: z.array(z.string()).optional(),
    draft: z.boolean().default(false),
  }),
});

const faqs = defineCollection({
  loader: glob({ pattern: '**/*.md', base: './src/content/faqs' }),
  schema: z.object({
    question: z.string(),
    answer: z.string(),
    order: z.number().default(0),
  }),
});

const testimonials = defineCollection({
  loader: glob({ pattern: '**/*.md', base: './src/content/testimonials' }),
  schema: z.object({
    quote: z.string(),
    author: z.string(),
    role: z.string(),
    company: z.string(),
    rating: z.number().min(1).max(5).default(5),
    sector: z.string().optional(),
    order: z.number().default(0),
    featured: z.boolean().default(false),
  }),
});

const features = defineCollection({
  loader: glob({ pattern: '**/*.md', base: './src/content/features' }),
  schema: z.object({
    title: z.string(),
    description: z.string(),
    icon: z.enum(['calendar', 'bell', 'users', 'credit', 'message', 'chart', 'smartphone', 'zap']).default('calendar'),
    order: z.number().default(0),
    sectors: z.array(z.string()).optional(),
  }),
});

const posts = defineCollection({
  loader: glob({ pattern: '**/*.md', base: './src/content/posts' }),
  schema: z.object({
    title: z.string(),
    description: z.string(),
    pubDate: z.date(),
    updatedDate: z.date().optional(),
    author: z.string().default('Agenda de Reservas Team'),
    category: z.enum(['productividad', 'marketing', 'tecnologia', 'sectores', 'tutoriales', 'noticias']),
    tags: z.array(z.string()).default([]),
    image: z.string().optional(),
    featured: z.boolean().default(false),
    draft: z.boolean().default(false),
    readingTime: z.number().optional(),
  }),
});

export const collections = { landings, faqs, testimonials, features, posts };
