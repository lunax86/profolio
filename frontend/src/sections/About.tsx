import { Icon } from '@/components/Icon';
import type { SiteSettings } from '@/lib/api';

// Sekce „O mně": u živnostníka nejsilnější prvek důvěry. Portrét + pár vět.
// Bez fotky ukážeme neutrální siluetu (žádný cizí obličej), ať je jasné „sem přijde fotka".
export function About({ settings }: { settings: SiteSettings }) {
    const text = settings.about_text;
    const image = settings.about_image;
    if (!text && !image) return null;

    const heading = settings.about_title || settings.site_title || 'O mně';

    return (
        <section id="o-mne" className="py-24">
            <div className="container">
                <div className="grid items-center gap-10 lg:grid-cols-2">
                    {image ? (
                        <img
                            src={image}
                            alt={heading}
                            loading="lazy"
                            className="mx-auto aspect-[4/5] w-full max-w-sm rounded-lg border border-border object-cover shadow-sm"
                        />
                    ) : (
                        <div className="mx-auto flex aspect-[4/5] w-full max-w-sm items-center justify-center rounded-lg border border-border bg-muted text-muted-foreground">
                            <Icon name="user" className="h-24 w-24" />
                        </div>
                    )}
                    <div>
                        <span className="text-sm font-semibold uppercase tracking-wider text-primary">
                            Pár slov o mně
                        </span>
                        <h2 className="mt-3 text-3xl font-bold sm:text-4xl">{heading}</h2>
                        {text && (
                            <p className="mt-5 whitespace-pre-line leading-relaxed text-muted-foreground">{text}</p>
                        )}
                    </div>
                </div>
            </div>
        </section>
    );
}
