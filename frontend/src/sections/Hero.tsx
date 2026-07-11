import { useEffect, useRef } from 'react';

import { Icon } from '@/components/Icon';
import { buttonVariants } from '@/components/ui/button';
import type { SiteSettings } from '@/lib/api';
import { cn } from '@/lib/utils';

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
        <section id="top" className="relative flex min-h-screen items-center overflow-hidden">
            {/* Parallax pozadí: vrstva je vyšší než hero, aby při posunu nevznikla dole mezera */}
            <div
                ref={parallaxLayerRef}
                className="absolute inset-x-0 top-0 h-[130%] bg-cover bg-center will-change-transform"
                style={{ backgroundImage: `url(${image})` }}
            />
            <div className="absolute inset-0 bg-gradient-to-b from-black/70 via-black/50 to-black/70" />

            <div className="container relative z-10 text-white">
                <h1 className="animate-fade-up text-4xl font-extrabold leading-[1.05] tracking-tight [text-wrap:balance] sm:text-5xl lg:text-6xl">
                    {settings.hero_title || settings.site_title || 'Rekonstrukce na klíč'}
                </h1>
                <div className="mt-7 grid items-start gap-10 lg:grid-cols-[1.15fr_0.85fr] lg:gap-14">
                    <div className="animate-fade-up" style={{ animationDelay: '0.1s', opacity: 0 }}>
                        {settings.hero_place && (
                            <span className="text-accent-hero block text-2xl font-bold sm:text-3xl lg:text-4xl">
                                {settings.hero_place}
                            </span>
                        )}
                        <p className="mt-4 max-w-md text-lg text-white/75">
                            {settings.slogan || 'Poctivě a s osobním přístupem, od návrhu po úklid.'}
                        </p>
                        <div className="mt-8 flex flex-col items-start gap-3">
                            {settings.contact_phone ? (
                                <>
                                    <a
                                        href={`tel:${settings.contact_phone.replace(/\s/g, '')}`}
                                        className={cn(buttonVariants({ size: 'lg' }))}
                                    >
                                        <Icon name="phone" className="h-5 w-5" />
                                        Zavolat {settings.contact_phone}
                                    </a>
                                    <a
                                        href="#poptavka"
                                        className="text-sm font-medium text-white/80 underline-offset-4 hover:text-white hover:underline"
                                    >
                                        …nebo mi napište
                                    </a>
                                </>
                            ) : (
                                <a href="#poptavka" className={cn(buttonVariants({ size: 'lg' }))}>
                                    Nezávazná poptávka
                                    <Icon name="arrow-right" className="h-5 w-5" />
                                </a>
                            )}
                        </div>
                    </div>

                    {settings.hero_about && (
                        <div
                            className="animate-fade-up lg:border-l lg:border-white/15 lg:pb-8 lg:pl-10 lg:pt-8"
                            style={{ animationDelay: '0.15s', opacity: 0 }}
                        >
                            <p className="text-accent-hero text-sm font-semibold uppercase tracking-[0.16em]">
                                Pár slov o mně
                            </p>
                            <p className="mt-3 max-w-sm leading-relaxed text-white/80">{settings.hero_about}</p>
                        </div>
                    )}
                </div>
            </div>

            <button
                type="button"
                onClick={() =>
                    document.getElementById('top')?.nextElementSibling?.scrollIntoView({ behavior: 'smooth' })
                }
                className="absolute bottom-8 left-1/2 z-10 -translate-x-1/2 text-white/80 transition-colors hover:text-white"
                aria-label="Posunout na další sekci"
            >
                <Icon name="chevron-down" className="h-8 w-8 animate-bounce" />
            </button>
        </section>
    );
}
