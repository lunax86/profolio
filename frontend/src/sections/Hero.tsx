import { Icon } from '@/components/Icon';
import { Button } from '@/components/ui/button';
import type { SiteSettings } from '@/lib/api';

const FALLBACK_IMAGE = 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=1920&q=80';

export function Hero({ settings }: { settings: SiteSettings }) {
    const image = settings.hero_image || FALLBACK_IMAGE;

    return (
        <section id="top" className="relative flex min-h-screen items-center justify-center overflow-hidden">
            {/* Parallax pozadí */}
            <div
                className="absolute inset-0 bg-cover bg-fixed bg-center"
                style={{ backgroundImage: `url(${image})` }}
            />
            <div className="absolute inset-0 bg-gradient-to-b from-black/70 via-black/50 to-black/70" />

            <div className="container relative z-10 flex flex-col items-center text-center text-white">
                <h1 className="max-w-3xl animate-fade-up text-4xl font-extrabold leading-tight tracking-tight sm:text-6xl">
                    {settings.hero_title ?? 'Kvalitní řešení na míru'}
                </h1>
                <p
                    className="mt-6 max-w-xl animate-fade-up text-lg text-white/85"
                    style={{ animationDelay: '0.1s', opacity: 0 }}
                >
                    {settings.hero_slogan ?? 'Od návrhu až po realizaci, spolehlivě a s důrazem na detail.'}
                </p>
                <div className="mt-10 animate-fade-up" style={{ animationDelay: '0.2s', opacity: 0 }}>
                    <Button size="lg" onClick={() => document.getElementById('poptavka')?.scrollIntoView()}>
                        Nezávazně poptat
                        <Icon name="arrow-right" className="h-5 w-5" />
                    </Button>
                </div>
            </div>

            <a
                href="#sluzby"
                className="absolute bottom-8 z-10 text-white/80 transition-colors hover:text-white"
                aria-label="Posunout dolů"
            >
                <Icon name="chevron-down" className="h-8 w-8 animate-bounce" />
            </a>
        </section>
    );
}
