import { Icon } from '@/components/Icon'
import type { SiteSettings } from '@/lib/api'

export function Footer({
  settings,
  onOpenPrivacy,
}: {
  settings: SiteSettings
  onOpenPrivacy: () => void
}) {
  const year = new Date().getFullYear()

  return (
    <footer id="kontakt" className="border-t border-border bg-card">
      <div className="container grid gap-10 py-16 sm:grid-cols-2 lg:grid-cols-3">
        <div>
          <h3 className="text-lg font-bold">{settings.site_title ?? 'Vaše firma'}</h3>
          <p className="mt-3 text-sm text-muted-foreground">
            Kvalitní řešení na míru od návrhu až po realizaci.
          </p>
        </div>

        <div>
          <h4 className="font-semibold">Kontakt</h4>
          <ul className="mt-4 space-y-3 text-sm text-muted-foreground">
            {settings.contact_email && (
              <li className="flex items-center gap-3">
                <Icon name="mail" className="h-4 w-4 text-primary" />
                <a href={`mailto:${settings.contact_email}`} className="hover:text-foreground">
                  {settings.contact_email}
                </a>
              </li>
            )}
            {settings.contact_phone && (
              <li className="flex items-center gap-3">
                <Icon name="phone" className="h-4 w-4 text-primary" />
                <a
                  href={`tel:${settings.contact_phone.replace(/\s/g, '')}`}
                  className="hover:text-foreground"
                >
                  {settings.contact_phone}
                </a>
              </li>
            )}
            {settings.contact_address && (
              <li className="flex items-center gap-3">
                <Icon name="map-pin" className="h-4 w-4 text-primary" />
                {settings.contact_address}
              </li>
            )}
          </ul>
        </div>

        <div>
          <h4 className="font-semibold">Sledujte nás</h4>
          <div className="mt-4 flex gap-3">
            {settings.social_facebook && (
              <a
                href={settings.social_facebook}
                target="_blank"
                rel="noreferrer"
                className="flex h-10 w-10 items-center justify-center rounded-lg border border-border transition-colors hover:bg-secondary"
                aria-label="Facebook"
              >
                <Icon name="facebook" className="h-5 w-5" />
              </a>
            )}
            {settings.social_instagram && (
              <a
                href={settings.social_instagram}
                target="_blank"
                rel="noreferrer"
                className="flex h-10 w-10 items-center justify-center rounded-lg border border-border transition-colors hover:bg-secondary"
                aria-label="Instagram"
              >
                <Icon name="instagram" className="h-5 w-5" />
              </a>
            )}
          </div>
        </div>
      </div>

      <div className="border-t border-border py-6 text-center text-sm text-muted-foreground">
        © {year} {settings.site_title ?? 'Vaše firma'}. Všechna práva vyhrazena.
        {' · '}
        <button
          type="button"
          onClick={onOpenPrivacy}
          className="underline underline-offset-2 transition-colors hover:text-foreground"
        >
          Zásady ochrany osobních údajů
        </button>
      </div>
    </footer>
  )
}
