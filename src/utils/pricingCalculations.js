/**
 * pricingCalculations.js
 * Lógica pura de cálculo de precios extraída de los artículos del blog.
 * Importable y testeable independientemente de Astro.
 */

/**
 * Construye la tabla de comparativa de precios por número de empleados.
 * Todos los valores vienen de la BD (via apiPricing / apiCompetitors).
 *
 * @param {object} opts
 * @param {number} opts.ourMonthly       - Nuestro precio mensual (service_business.monthly)
 * @param {number} opts.agendaproPPE     - AgendaPro: precio por empleado (price_per_employee)
 * @param {number} opts.reservioBase     - Reservio: precio base 1 empleado (base_price)
 * @param {number} opts.reservioPPS      - Reservio: precio por empleado adicional (price_per_staff)
 * @param {number[]} [opts.sizes]        - Tamaños de equipo a calcular (default: [1, 3, 5])
 * @returns {Array<{esteticistas, agendadereservas, agendapro, reservio, savingVsAgendaproAnnual}>}
 */
export function buildPricingTable({
  ourMonthly,
  agendaproPPE,
  reservioBase,
  reservioPPS,
  sizes = [1, 3, 5],
}) {
  return sizes.map((n) => ({
    esteticistas:              n,
    agendadereservas:          ourMonthly,
    agendapro:                 agendaproPPE * n,
    reservio:                  Math.round(reservioBase + (n - 1) * reservioPPS),
    savingVsAgendaproAnnual:   (agendaproPPE * n - ourMonthly) * 12,
  }));
}

/**
 * Calcula el ahorro anual entre nuestro precio y el de un competidor mensual.
 * Usado por `data-price-saving-annual="service_business.monthly:147"`.
 *
 * @param {number} ourMonthly        - Nuestro precio mensual
 * @param {number} competitorMonthly - Precio mensual del competidor
 * @returns {number}                 - Ahorro en euros por año
 */
export function annualSaving(ourMonthly, competitorMonthly) {
  return (competitorMonthly - ourMonthly) * 12;
}

/**
 * Aplica fallbacks de la API de precios.
 * Simula el merge que hace el build-time fetch en Astro.
 *
 * @param {object|null} apiData    - Datos recibidos de la API (puede ser null si falla)
 * @param {object} fallbackPricing - Valores por defecto de pricing_config
 * @param {object} fallbackCompetitors - Valores por defecto de competitor_config
 * @returns {{ pricing, competitors }}
 */
export function resolvePricing(apiData, fallbackPricing, fallbackCompetitors) {
  return {
    pricing:     apiData?.pricing     ? { ...fallbackPricing,     ...apiData.pricing     } : fallbackPricing,
    competitors: apiData?.competitors ? { ...fallbackCompetitors, ...apiData.competitors } : fallbackCompetitors,
  };
}

// Valores de fallback (iguales a los hardcodeados en los artículos Astro)
export const DEFAULT_PRICING = {
  service_business:     { monthly: 19, yearly: 190 },
  restaurant:           { monthly: 24, yearly: 240 },
  tourism_activities:   { monthly: 29, yearly: 290 },
  addon_communications: { monthly: 8,  yearly: 80  },
};

export const DEFAULT_COMPETITORS = {
  agendapro: { price_per_employee: 49 },
  reservio:  { base_price: 31, price_per_staff: 26.25 },
};
