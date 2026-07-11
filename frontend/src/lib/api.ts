/** Klient pro komunikaci s PHP API. V devu jde přes Vite proxy na :8000. */

export interface SiteSettings {
    site_title?: string;
    /** Obecný jednořádkový popis; základ pro hero podnadpis, patičku i SEO. */
    slogan?: string;
    hero_title?: string;
    hero_place?: string;
    hero_about?: string;
    /** Nenápadný odkaz pod „o mně" v heru: '' | 'portfolio' | 'instagram' (jen když je sekce zapnutá). */
    hero_link?: string;
    hero_image?: string;
    footer_tagline?: string;
    footer_portrait?: string;
    contact_email?: string;
    contact_phone?: string;
    contact_address?: string;
    social_facebook?: string;
    social_instagram?: string;
    privacy_policy?: string;
    /** JSON pole modulárních sekcí v pořadí: [{ key, enabled }] */
    sections?: string;
}

export interface SectionConfig {
    key: string;
    enabled: boolean;
}

/** Modulární sekce, které lze v administraci zapnout/vypnout a přeuspořádat. Hero a Footer jsou fixní. */
export const MODULAR_SECTIONS = ['portfolio', 'services', 'reviews', 'inquiry', 'instagram'] as const;
export type ModularSectionKey = (typeof MODULAR_SECTIONS)[number];

const DEFAULT_SECTIONS: SectionConfig[] = [
    { key: 'portfolio', enabled: true },
    { key: 'services', enabled: true },
    { key: 'reviews', enabled: true },
    { key: 'inquiry', enabled: true },
    { key: 'instagram', enabled: false },
];

/**
 * Rozparsuje uložené pořadí a viditelnost sekcí (JSON z nastavení). Ponechá jen známé
 * klíče a doplní chybějící (nová sekce přidaná do buildu) na konec jako vypnutou.
 * Při nevalidním nebo prázdném vstupu spadne na výchozí pořadí.
 */
export function parseSections(raw?: string): SectionConfig[] {
    let stored: SectionConfig[] = [];
    if (raw) {
        try {
            const parsed: unknown = JSON.parse(raw);
            if (Array.isArray(parsed)) {
                stored = parsed
                    .filter(
                        (item): item is SectionConfig =>
                            typeof item?.key === 'string' && MODULAR_SECTIONS.includes(item.key as ModularSectionKey),
                    )
                    .map((item) => ({ key: item.key, enabled: Boolean(item.enabled) }));
            }
        } catch {
            // nevalidní JSON → výchozí pořadí níže
        }
    }
    if (stored.length === 0) return DEFAULT_SECTIONS;

    const storedKeys = new Set(stored.map((item) => item.key));
    const missing = MODULAR_SECTIONS.filter((key) => !storedKeys.has(key)).map((key) => ({ key, enabled: false }));
    return [...stored, ...missing];
}

export interface Service {
    id: number;
    title: string;
    description: string;
    icon: string;
    sort_order: number;
}

export interface PortfolioItem {
    id: number;
    title: string;
    description: string;
    image_path: string;
    /** volitelná fotka „před"; když je vyplněná, ukáže se posuvník před/po */
    image_before?: string;
    sort_order: number;
}

export interface Testimonial {
    id: number;
    author: string;
    text: string;
    role: string;
    sort_order: number;
}

export interface InquiryPayload {
    name: string;
    email: string;
    phone?: string;
    message?: string;
    /** honeypot - skryté pole, člověk ho nechá prázdné */
    website?: string;
    /** time-trap - sekundy od načtení formuláře */
    elapsed?: number;
}

async function get<T>(path: string): Promise<T> {
    const res = await fetch(path);
    if (!res.ok) throw new Error(`Chyba API ${res.status}`);
    return res.json() as Promise<T>;
}

export const api = {
    settings: () => get<SiteSettings>('/api/settings'),
    services: () => get<Service[]>('/api/services'),
    portfolio: () => get<PortfolioItem[]>('/api/portfolio'),
    testimonials: () => get<Testimonial[]>('/api/testimonials'),
    /** Anonymní záznam návštěvy - bez čekání na odpověď, chyby ignorujeme. */
    hit: (): void => {
        void fetch('/api/hit', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ref: document.referrer || '' }),
        }).catch(() => {});
    },
    async sendInquiry(payload: InquiryPayload): Promise<{ message: string }> {
        const res = await fetch('/api/inquiries', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.error ?? 'Odeslání selhalo');
        return data;
    },
};
