import { Icon } from '@/components/Icon';
import { buttonVariants } from '@/components/ui/button';
import type { SiteSettings } from '@/lib/api';
import { cn } from '@/lib/utils';

// Vlastní modul pro Instagram (řízený settings.social_instagram, jinak se nevykreslí).
// Každá síť má mít vlastní modul, ne sdílenou „Sociální sítě" sekci.
export function Instagram({ settings }: { settings: SiteSettings }) {
    const instagram = settings.social_instagram;
    if (!instagram) return null;

    return (
        <section id="instagram" className="py-24">
            <div className="container">
                <div className="mx-auto flex max-w-2xl flex-col items-center text-center">
                    <span className="flex h-16 w-16 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                        <Icon name="instagram" className="h-8 w-8" />
                    </span>
                    <span className="mt-6 text-sm font-semibold uppercase tracking-wider text-primary">
                        Buďme v kontaktu
                    </span>
                    <h2 className="mt-3 text-3xl font-bold sm:text-4xl">Sledujte nás na Instagramu</h2>
                    <p className="mt-4 text-muted-foreground">
                        Nejnovější fotky, ukázky a zákulisí najdete na našem profilu.
                    </p>
                    <a
                        href={instagram}
                        target="_blank"
                        rel="noreferrer"
                        className={cn(buttonVariants({ size: 'lg' }), 'mt-8')}
                    >
                        <Icon name="instagram" className="h-5 w-5" />
                        Otevřít Instagram
                    </a>
                </div>
            </div>
        </section>
    );
}
