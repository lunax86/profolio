/** Klient pro komunikaci s PHP API. V devu jde přes Vite proxy na :8000. */

export interface SiteSettings {
  site_title?: string
  hero_title?: string
  hero_slogan?: string
  hero_image?: string
  contact_email?: string
  contact_phone?: string
  contact_address?: string
  social_facebook?: string
  social_instagram?: string
}

export interface Service {
  id: number
  title: string
  description: string
  icon: string
  sort_order: number
}

export interface PortfolioItem {
  id: number
  title: string
  description: string
  image_path: string
  sort_order: number
}

export interface InquiryPayload {
  name: string
  email: string
  phone?: string
  message?: string
  /** honeypot – skryté pole, člověk ho nechá prázdné */
  website?: string
  /** time-trap – sekundy od načtení formuláře */
  elapsed?: number
}

async function get<T>(path: string): Promise<T> {
  const res = await fetch(path)
  if (!res.ok) throw new Error(`Chyba API ${res.status}`)
  return res.json() as Promise<T>
}

export const api = {
  settings: () => get<SiteSettings>('/api/settings'),
  services: () => get<Service[]>('/api/services'),
  portfolio: () => get<PortfolioItem[]>('/api/portfolio'),
  /** Anonymní záznam návštěvy – bez čekání na odpověď, chyby ignorujeme. */
  hit: (): void => {
    void fetch('/api/hit', { method: 'POST' }).catch(() => {})
  },
  async sendInquiry(payload: InquiryPayload): Promise<{ message: string }> {
    const res = await fetch('/api/inquiries', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    })
    const data = await res.json()
    if (!res.ok) throw new Error(data.error ?? 'Odeslání selhalo')
    return data
  },
}
