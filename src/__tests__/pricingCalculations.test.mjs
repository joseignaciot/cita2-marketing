/**
 * pricingCalculations.test.mjs
 * Tests de la lógica de cálculo de precios del marketing site.
 * Ejecución: node --test src/__tests__/pricingCalculations.test.mjs
 */

import { describe, it } from 'node:test';
import assert from 'node:assert/strict';

// ─── Importar el módulo bajo test ────────────────────────────────────────────
import {
  buildPricingTable,
  annualSaving,
  resolvePricing,
  DEFAULT_PRICING,
  DEFAULT_COMPETITORS,
} from '../utils/pricingCalculations.js';

// ─── Constantes de referencia ────────────────────────────────────────────────
const OUR_PRICE     = 19;
const AGENDAPRO_PPE = 49;
const RESERVIO_BASE = 31;
const RESERVIO_PPS  = 26.25;

// ─── 1. Tabla de comparativa de estética ─────────────────────────────────────
describe('buildPricingTable – tabla comparativa estética', () => {
  const table = buildPricingTable({
    ourMonthly:   OUR_PRICE,
    agendaproPPE: AGENDAPRO_PPE,
    reservioBase: RESERVIO_BASE,
    reservioPPS:  RESERVIO_PPS,
  });

  it('genera 3 filas para tamaños [1, 3, 5]', () => {
    assert.equal(table.length, 3);
  });

  it('nuestro precio es siempre 19€ sin importar nº de empleados', () => {
    for (const row of table) assert.equal(row.agendadereservas, OUR_PRICE);
  });

  it('AgendaPro: precio × nº empleados exacto', () => {
    assert.equal(table[0].agendapro, 49);   // 1 empleado
    assert.equal(table[1].agendapro, 147);  // 3 empleados
    assert.equal(table[2].agendapro, 245);  // 5 empleados
  });

  it('Reservio: base + (n-1) × price_per_staff redondeado — valores exactos', () => {
    assert.equal(table[0].reservio, 31);  // 31 + 0 = 31
    assert.equal(table[1].reservio, 84);  // 31 + 2×26.25 = 83.5 → 84
    assert.equal(table[2].reservio, 136); // 31 + 4×26.25 = 136
  });

  it('ahorro anual vs AgendaPro es correcto (diferencia mensual × 12)', () => {
    assert.equal(table[0].savingVsAgendaproAnnual, (49  - 19) * 12); // 360
    assert.equal(table[1].savingVsAgendaproAnnual, (147 - 19) * 12); // 1.536
    assert.equal(table[2].savingVsAgendaproAnnual, (245 - 19) * 12); // 2.712
  });

  it('ningún ahorro es negativo (siempre somos más baratos que AgendaPro)', () => {
    for (const row of table) assert.ok(row.savingVsAgendaproAnnual > 0);
  });

  it('siempre somos más baratos que AgendaPro en cuota mensual', () => {
    for (const row of table) assert.ok(row.agendadereservas < row.agendapro);
  });
});

// ─── 2. annualSaving – cálculo de ahorro anual (data-price-saving-annual) ────
describe('annualSaving – ahorro anual en banners comparativos', () => {
  it('(147 - 19) × 12 = 1.536€ — caso peluquerías vs AgendaPro', () => {
    assert.equal(annualSaving(19, 147), 1536);
  });

  it('(49 - 19) × 12 = 360€ — 1 empleado', () => {
    assert.equal(annualSaving(19, 49), 360);
  });

  it('(245 - 19) × 12 = 2.712€ — 5 empleados vs AgendaPro', () => {
    assert.equal(annualSaving(19, 245), 2712);
  });

  it('restaurantes (24€) vs TheFork (~175€): (175 - 24) × 12 = 1.812€', () => {
    assert.equal(annualSaving(24, 175), 1812);
  });

  it('el resultado es siempre positivo cuando nuestro precio es menor', () => {
    assert.ok(annualSaving(19, 147) > 0);
    assert.ok(annualSaving(24, 175) > 0);
    assert.ok(annualSaving(29, 50) > 0); // turismo
  });

  it('si somos más caros, el resultado es negativo (sin subsidio)', () => {
    assert.ok(annualSaving(100, 50) < 0);
  });
});

// ─── 3. resolvePricing – merge API + fallbacks ────────────────────────────────
describe('resolvePricing – merge datos de API con valores por defecto', () => {
  it('usa fallbacks completos cuando la API devuelve null', () => {
    const { pricing, competitors } = resolvePricing(null, DEFAULT_PRICING, DEFAULT_COMPETITORS);
    assert.equal(pricing.service_business.monthly, 19);
    assert.equal(pricing.restaurant.monthly, 24);
    assert.equal(competitors.agendapro.price_per_employee, 49);
    assert.equal(competitors.reservio.base_price, 31);
  });

  it('override correcto cuando la API sube nuestro precio', () => {
    const apiData = {
      pricing:     { service_business: { monthly: 22, yearly: 220 } },
      competitors: {},
    };
    const { pricing } = resolvePricing(apiData, DEFAULT_PRICING, DEFAULT_COMPETITORS);
    assert.equal(pricing.service_business.monthly, 22);
    assert.equal(pricing.restaurant.monthly, 24); // sin cambiar
  });

  it('override de competitor mantiene otros del fallback', () => {
    const apiData = {
      pricing:     {},
      competitors: { agendapro: { price_per_employee: 55 } },
    };
    const { competitors } = resolvePricing(apiData, DEFAULT_PRICING, DEFAULT_COMPETITORS);
    assert.equal(competitors.agendapro.price_per_employee, 55); // actualizado
    assert.equal(competitors.reservio.base_price, 31);           // fallback intacto
  });

  it('merge no pierde claves de fallback no presentes en apiData.pricing', () => {
    const apiData = { pricing: { service_business: { monthly: 25, yearly: 250 } } };
    const { pricing } = resolvePricing(apiData, DEFAULT_PRICING, DEFAULT_COMPETITORS);
    assert.equal(pricing.addon_communications.monthly, 8);
    assert.equal(pricing.restaurant.monthly, 24);
    assert.equal(pricing.tourism_activities.monthly, 29);
  });

  it('respuesta sin competitors mantiene fallback completo', () => {
    const apiData = { pricing: {} };
    const { competitors } = resolvePricing(apiData, DEFAULT_PRICING, DEFAULT_COMPETITORS);
    assert.equal(competitors.agendapro.price_per_employee, 49);
    assert.equal(competitors.reservio.price_per_staff, 26.25);
  });
});

// ─── 4. DEFAULT_PRICING – integridad de valores actuales de la BD ─────────────
describe('DEFAULT_PRICING – valores actuales en system_settings', () => {
  it('service_business.monthly = 19€', () => {
    assert.equal(DEFAULT_PRICING.service_business.monthly, 19);
  });

  it('restaurant.monthly = 24€', () => {
    assert.equal(DEFAULT_PRICING.restaurant.monthly, 24);
  });

  it('tourism_activities.monthly = 29€', () => {
    assert.equal(DEFAULT_PRICING.tourism_activities.monthly, 29);
  });

  it('addon_communications.monthly = 8€', () => {
    assert.equal(DEFAULT_PRICING.addon_communications.monthly, 8);
  });

  it('yearly equivale a 10 meses (descuento ~17%)', () => {
    // Plan anual = 10 meses pagados
    assert.equal(DEFAULT_PRICING.service_business.yearly,
                 DEFAULT_PRICING.service_business.monthly * 10);
    assert.equal(DEFAULT_PRICING.restaurant.yearly,
                 DEFAULT_PRICING.restaurant.monthly * 10);
    assert.equal(DEFAULT_PRICING.tourism_activities.yearly,
                 DEFAULT_PRICING.tourism_activities.monthly * 10);
  });

  it('addon_communications.yearly equivale a 10 meses', () => {
    assert.equal(DEFAULT_PRICING.addon_communications.yearly,
                 DEFAULT_PRICING.addon_communications.monthly * 10);
  });
});

// ─── 5. DEFAULT_COMPETITORS – coherencia de datos ─────────────────────────────
describe('DEFAULT_COMPETITORS – integridad de datos de competidores', () => {
  it('AgendaPro price_per_employee = 49€', () => {
    assert.equal(DEFAULT_COMPETITORS.agendapro.price_per_employee, 49);
  });

  it('Reservio base_price = 31€', () => {
    assert.equal(DEFAULT_COMPETITORS.reservio.base_price, 31);
  });

  it('Reservio price_per_staff = 26.25€', () => {
    assert.equal(DEFAULT_COMPETITORS.reservio.price_per_staff, 26.25);
  });

  it('Reservio 5 empleados = 136€ exactos con price_per_staff correcto', () => {
    const { base_price, price_per_staff } = DEFAULT_COMPETITORS.reservio;
    assert.equal(Math.round(base_price + (5 - 1) * price_per_staff), 136);
  });
});

// ─── 6. buildPricingTable con tamaños custom ──────────────────────────────────
describe('buildPricingTable – tamaños de equipo custom', () => {
  it('funciona con tamaños [2, 4, 6]', () => {
    const table = buildPricingTable({
      ourMonthly: 19, agendaproPPE: 49,
      reservioBase: 31, reservioPPS: 26.25,
      sizes: [2, 4, 6],
    });
    assert.equal(table.length, 3);
    assert.equal(table[0].agendapro, 98);  // 49 × 2
    assert.equal(table[1].agendapro, 196); // 49 × 4
    assert.equal(table[2].agendapro, 294); // 49 × 6
  });

  it('nuestro precio es siempre flat independientemente del tamaño', () => {
    const table = buildPricingTable({
      ourMonthly: 19, agendaproPPE: 49,
      reservioBase: 31, reservioPPS: 26.25,
      sizes: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
    });
    for (const row of table) assert.equal(row.agendadereservas, 19);
  });
});
