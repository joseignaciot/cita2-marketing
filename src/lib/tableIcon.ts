// Pre-rendered Lucide icon markup for comparison-table cells.
//
// Those cells are plain strings (rendered with `set:html`), not Astro
// markup, so the <Icon> component can't be used there directly. This
// keeps the exact same path data, stroke-width and sizing as Icon.astro
// for the handful of icons those tables need (check / cross / warning /
// trophy), without importing the whole icon set.
const ICONS = {
  check: '<path d="m9 12 2 2 4-4"/><circle cx="12" cy="12" r="10"/>',
  x: '<circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/>',
  warn: '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/>',
  trophy:
    '<path d="M10 14.66v1.626a2 2 0 0 1-.976 1.696A5 5 0 0 0 7 21.978"/><path d="M14 14.66v1.626a2 2 0 0 0 .976 1.696A5 5 0 0 1 17 21.978"/><path d="M18 9h1.5a1 1 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M6 9a6 6 0 0 0 12 0V3a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1z"/><path d="M6 9H4.5a1 1 0 0 1 0-5H6"/>',
  zap: '<path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"/>',
} as const;

export type TableIconName = keyof typeof ICONS;

export function tableIcon(name: TableIconName, className = '', size = 16): string {
  return `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="lucide lucide-${name} inline-block shrink-0 align-[-0.125em]${className ? ` ${className}` : ''}">${ICONS[name]}</svg>`;
}
