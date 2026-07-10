import { useEffect, useRef } from 'react';

import { Icon } from '@/components/Icon';
import { Button } from '@/components/ui/button';
import type { SiteSettings } from '@/lib/api';

const FALLBACK_IMAGE = 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=1920&q=80';

// Jak silně pozadí „zaostává" za scrollem (0 = statické, 1 = jede se stránkou).
const PARALLAX_FACTOR = 0.4;

export function Hero({ settings }: { settings: SiteSettings }) {
    const image = settings.hero_image || FALLBACK_IMAGE;
    const parallaxLayerRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        const parallaxLayer = parallaxLayerRef.current;
        if (!parallaxLayer) return;

        // Respektuj systémové nastavení „omezit pohyb", parallax pak vypni.
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

        let frameRequested = false;
        const onScroll = () => {
            if (frameRequested) return; // sloučit víc scroll událostí do jednoho frame
            frameRequested = true;
            requestAnimationFrame(() => {
                const offset = window.scrollY * PARALLAX_FACTOR;
                // transform běží na kompozitoru (GPU) → žádné repainty, plynulé i na iOS
                parallaxLayer.style.transform = `translate3d(0, ${offset}px, 0)`;
                frameRequested = false;
            });
        };

        window.addEventListener('scroll', onScroll, { passive: true });
        return () => window.removeEventListener('scroll', onScroll);
    }, []);

    return (
        <section id="top" className="relative flex min-h-screen items-center justify-center overflow-hidden">
            {/* Parallax pozadí: vrstva je vyšší než hero, aby při posunu nevznikla dole mezera */}
            <div
                ref={parallaxLayerRef}
                className="absolute inset-x-0 top-0 h-[130%] bg-cover bg-center will-change-transform"
                style={{ backgroundImage: `url(${image})` }}
            />
            <div className="absolute inset-0 bg-gradient-to-b from-black/70 via-black/50 to-black/70" />

            <div className="container relative z-10 flex flex-col items-center text-center text-white">
                <h1 className="animate-fade-up text-4xl font-extrabold leading-tight tracking-tight [text-wrap:balance] sm:text-6xl">
                    {settings.hero_title || settings.site_title || 'Kvalitní řešení na míru'}
                </h1>
                <p
                    className="mt-6 max-w-xl animate-fade-up text-lg text-white/85"
                    style={{ animationDelay: '0.1s', opacity: 0 }}
                >
                    {settings.hero_slogan ||
                        settings.slogan ||
                        'Od návrhu až po realizaci, spolehlivě a s důrazem na detail.'}
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
