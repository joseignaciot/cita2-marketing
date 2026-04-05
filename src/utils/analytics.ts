// Google Analytics 4 + Meta Pixel Tracking
// Este script debe incluirse en el <head> del Layout.astro

// Google Analytics 4 Configuration
export const GA4_CONFIG = {
  measurementId: 'G-XXXXXXXXXX', // Reemplazar con el ID real de GA4
  debug_mode: false,
  send_page_view: true,
  allow_google_signals: true,
  allow_ad_personalization_signals: true,
  cookie_flags: 'SameSite=None;Secure',
  custom_map: {
    'custom_parameter_1': 'plan_type',
    'custom_parameter_2': 'user_type'
  }
};

// Meta Pixel Configuration
export const META_PIXEL_CONFIG = {
  pixelId: 'XXXXXXXXXXXXXXXX', // Reemplazar con el ID real de Meta Pixel
  advanced_matching: true,
  automatic_matching: true
};

// Eventos de conversión a trackear
export const TRACKING_EVENTS = {
  // Conversiones principales
  SIGNUP_STARTED: 'signup_started',
  SIGNUP_COMPLETED: 'signup_completed',
  TRIAL_STARTED: 'trial_started',
  SUBSCRIPTION_COMPLETED: 'subscription_completed',
  
  // Engagement
  PAGE_VIEW: 'page_view',
  PRICING_VIEWED: 'pricing_viewed',
  FEATURE_INTEREST: 'feature_interest',
  DEMO_REQUESTED: 'demo_requested',
  FAQ_EXPANDED: 'faq_expanded',
  
  // Scroll depth
  SCROLL_25: 'scroll_25',
  SCROLL_50: 'scroll_50',
  SCROLL_75: 'scroll_75',
  SCROLL_100: 'scroll_100',
  
  // CTA clicks
  PRIMARY_CTA_CLICK: 'primary_cta_click',
  SECONDARY_CTA_CLICK: 'secondary_cta_click',
  NAVIGATION_CLICK: 'navigation_click',
  
  // Comparativas
  COMPARISON_VIEWED: 'comparison_viewed',
  COMPETITOR_COMPARED: 'competitor_compared',
  
  // Blog
  ARTICLE_VIEWED: 'article_viewed',
  ARTICLE_READ_TIME: 'article_read_time'
};

// Función para inicializar GA4
export function initGA4(measurementId: string) {
  const script = `
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '${measurementId}', {
      page_title: document.title,
      page_location: window.location.href,
      send_page_view: true
    });
  `;
  return script;
}

// Función para inicializar Meta Pixel
export function initMetaPixel(pixelId: string) {
  const script = `
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '${pixelId}');
    fbq('track', 'PageView');
  `;
  return script;
}

// Event tracking helper
export function trackEvent(eventName: string, parameters?: Record<string, any>) {
  // GA4
  if (typeof window !== 'undefined' && (window as any).gtag) {
    (window as any).gtag('event', eventName, parameters);
  }
  
  // Meta Pixel
  if (typeof window !== 'undefined' && (window as any).fbq) {
    (window as any).fbq('track', eventName, parameters);
  }
}

// Scroll depth tracking
export function initScrollTracking() {
  if (typeof window === 'undefined') return;
  
  const scrollMarks = [25, 50, 75, 100];
  const triggeredMarks: number[] = [];
  
  const trackScroll = () => {
    const scrollPercent = Math.round(
      (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100
    );
    
    scrollMarks.forEach(mark => {
      if (scrollPercent >= mark && !triggeredMarks.includes(mark)) {
        triggeredMarks.push(mark);
        trackEvent(`scroll_${mark}`, {
          page_path: window.location.pathname,
          scroll_depth: mark
        });
      }
    });
  };
  
  window.addEventListener('scroll', trackScroll, { passive: true });
}

// CTA click tracking
export function trackCTAClick(ctaType: 'primary' | 'secondary', ctaText: string, location: string) {
  trackEvent(TRACKING_EVENTS.PRIMARY_CTA_CLICK, {
    cta_type: ctaType,
    cta_text: ctaText,
    location: location,
    page_path: typeof window !== 'undefined' ? window.location.pathname : ''
  });
}

// Pricing interaction tracking
export function trackPricingView(planName: string, price: number) {
  trackEvent(TRACKING_EVENTS.PRICING_VIEWED, {
    plan_name: planName,
    price: price,
    currency: 'EUR'
  });
}

// Blog article tracking
export function trackArticleView(articleSlug: string, articleTitle: string, readTime: number) {
  trackEvent(TRACKING_EVENTS.ARTICLE_VIEWED, {
    article_slug: articleSlug,
    article_title: articleTitle,
    estimated_read_time: readTime,
    author: 'Cita2 Team'
  });
}

// Conversion tracking
export function trackConversion(conversionType: string, value?: number) {
  trackEvent(conversionType, {
    value: value || 0,
    currency: 'EUR'
  });
  
  // Meta Pixel specific conversion events
  if (typeof window !== 'undefined' && (window as any).fbq) {
    if (conversionType === TRACKING_EVENTS.SIGNUP_COMPLETED) {
      (window as any).fbq('track', 'CompleteRegistration');
    } else if (conversionType === TRACKING_EVENTS.SUBSCRIPTION_COMPLETED) {
      (window as any).fbq('track', 'Purchase', {
        value: value,
        currency: 'EUR'
      });
    }
  }
}
