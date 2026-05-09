import { describe, it, expect } from 'vitest';
import {
  buildPricingTable,
  annualSaving,
  resolvePricing,
  DEFAULT_PRICING,
  DEFAULT_COMPETITORS,
} from '../../src/utils/pricingCalculations.js';

// ─── Constantes de referencia ────────────────────────────────────────────────
const OUR_PRICE   = 19;   // service_business.monthly
const REST_PRICE  = 24;   // restaurant.monthly
const ADDON_PRICE = 8;    // addon_communications.monthly
const AGENDAPRO_PPE = 49; // precio por empleado
const RESERVIO_BASE = 31; // base
const RESERVIO_PPS  = 26.25; // por empleado adicional

// ─── 1. Tabla de comparativa de estética ────────────────────────────────────
describe('buildPricingTable – tabla estética', () => {
  const table = buildPricingTable({
    ourMonthly:   OUR_PRICE,
    agendaproPPE: AGENDAPRO_PPE,
    reservioBase: RESERVIO_BASE,
    reservioPPS:  RESERVIO_PPS,
  });

  it('genera 3 filas para los tamaños [1, 3, 5]', () => {
    expect(table).toHaveLength(3);
  });

  it('nuestro precio es siempre 19€ independientemente del nº de empleados', () => {
    table.forEach(row => expect(row.agendadereservas).toBe(OUR_PRICE));
  });

  it('AgendaPro: precio × nº empleados', () => {
    expect(table[0].agendapro).toBe(49);   // 1 empleado
    expect(table[1].agendapro).toBe(147);  // 3 empleados
    expect(table[2].agendapro).toBe(245);  // 5 empleados
  });

  it('Reservio: base + (n-1) × price_per_staff redondeado', () => {
    expect(table[0].reservio).toBe(31);   // 31 + 0
    expect(table[1].reservio).toBe(84);   // 31 + 2×26.25 = 83.5 → 84
    expect(table[2].reservio).toBe(136);  // 31 + 4×26.25 = 136
  });

  it('ahorro anual vs AgendaPro es correcto', () => {
    // (agendapro_mensual - nuestro) × 12
    expect(table[0].savingVsAgendaproAnnual).toBe((49  - 19) * 12); // 360
    expect(table[1].savingVsAgendaproAnnual).toBe((147 - 19) * 12); // 1.536
    expect(table[2].savingVsAgendaproAnnual).toBe((245 - 19) * 12); // 2.712
  });

  it('ningún valor de ahorro anual es negativo', () => {
    table.forEach(row => expect(row.savingVsAgendaproAnnual).toBeGreaterThan(0));
  });
});

// ─── 2. Cálculo annualSaving (data-price-saving-annual) ─────────────────────
describe('annualSaving – ahorro anual en banners', () => {
  it('(147 - 19) × 12 = 1.536€ (comparativa peluquerías)', () => {
    expect(annualSaving(19, 147)).toBe(1536);
  });

  it('(49 - 19) × 12 = 360€ (1 empleado)', () => {
    expect(annualSaving(19, 49)).toBe(360);
  });

  it('con precio restaurante (24€): (175 - 24) × 12 = 1.812€ vs TheFork', () => {
    expect(annualSaving(24, 175)).toBe(1812);
  });

  it('resultado siempre positivo cuando nuestro precio es menor', () => {
    expect(annualSaving(19, 147)).toBeGreaterThan(0);
    expect(annualSaving(24, 175)).toBeGreaterThan(0);
  });
});

// ─── 3. resolvePricing – merge API + fallbacks ───────────────────────────────
describe('resolvePricing – merge de datos de API con fallbacks', () => {
  it('usa fallbacks cuando la API devuelve null', () => {
    const { pricing, competitors } = resolvePricing(null, DEFAULT_PRICING, DEFAULT_COMPETITORS);
    expect(pricing.service_business.monthly).toBe(19);
    expect(competitors.agendapro.price_per_employee).toBe(49);
  });

  it('override correcto cuando la API responde con precio nuevo', () => {
    const apiData = {
      pricing:     { service_business: { monthly: 22, yearly: 220 } },
      competitors: { agendapro: { price_per_employee: 52 } },
    };
    const { pricing, competitors } = resolvePricing(apiData, DEFAULT_PRICING, DEFAULT_COMPETITORS);
    expect(pricing.service_business.monthly).toBe(22);
    expect(competitors.agendapro.price_per_employee).toBe(52);
    // Los demás precios se mantienen del fallback
    expect(pricing.restaurant.monthly).toBe(24);
    expect(competitors.reservio.base_price).toBe(31);
  });

  it('override parcial de competitors mantiene reservio del fallback', () => {
    const apiData = {
      pricing:     {},
      competitors: { agendapro: { price_per_employee: 55 } },
    };
    const { competitors } = resolvePricing(apiData, DEFAULT_PRICING, DEFAULT_COMPETITORS);
    expect(competitors.agendapro.price_per_employee).toBe(55);
    expect(competitors.reservio.base_price).toBe(31); // sin cambiar
  });

  it('merge no pierde claves del DEFAULT_PRICING ausentes en apiData', () => {
    const apiData = { pricing: { service_business: { monthly: 25, yearly: 250 } } };
    const { pricing } = resolvePricing(apiData, DEFAULT_PRICING, DEFAULT_COMPETITORS);
    expect(pricing.addon_communications.monthly).toBe(8); // fallback preservado
    expect(pricing.restaurant.monthly).toBe(24);          // fallback preservado
  });
});

// ─── 4. Valores de DEFAULT_PRICING ───────────────────────────────────────────
describe('DEFAULT_PRICING – valores actuales de la BD', () => {
  it('service_business.monthly = 19€', () => {
    expect(DEFAULT_PRICING.service_business.monthly).toBe(19);
  });

  it('restaurant.monthly = 24€', () => {
    expect(DEFAULT_PRICING.restaurant.monthly).toBe(24);
  });

  it('tourism_activities.monthly = 29€', () => {
    expect(DEFAULT_PRICING.tourism_activities.monthly).toBe(29);
  });

  it('addon_communications.monthly = 8€', () => {
    expect(DEFAULT_PRICING.addon_communications.monthly).toBe(8);
  });

  it('yearly = monthly × 10 (descuento ~17%)', () => {
    // El plan anual es aproximadamente 10 meses en lugar de 12
    expect(DEFAULT_PRICING.service_business.yearly).toBe(
      DEFAULT_PRICING.service_business.monthly * 10
    );
    expect(DEFAULT_PRICING.restaurant.yearly).toBe(
      DEFAULT_PRICING.restaurant.monthly * 10
    );
  });
});

// ─── 5. Coherencia entre tamaños de tabla ────────────────────────────────────
describe('buildPricingTable – coherencia con tamaños custom', () => {
  it('funciona con tamaños [1, 2, 4]', () => {
    const table = buildPricingTable({
      ourMonthly: 19, agendaproPPE: 49, reservioBase: 31, reservioPPS: 26.25,
      sizes: [1, 2, 4],
    });
    expect(table).toHaveLength(3);
    expect(table[1].agendapro).toBe(98);   // 49 × 2
    expect(table[2].agendapro).toBe(196);  // 49 × 4
    expect(table[0].agendadereservas).toBe(19); // siempre flat
    expect(table[2].agendadereservas).toBe(19); // siempre flat
  });

  it('nuestro precio es siempre menor que AgendaPro para n≥1', () => {
    const table = buildPricingTable({
      ourMonthly: 19, agendaproPPE: 49, reservioBase: 31, reservioPPS: 26.25,
    });
    table.forEach(row => expect(row.agendadereservas).toBeLessThan(row.agendapro));
  });
});
