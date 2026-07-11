import { useEffect, useRef } from 'react';

import { Icon } from '@/components/Icon';
import { buttonVariants } from '@/components/ui/button';
import type { SiteSettings } from '@/lib/api';
import { cn } from '@/lib/utils';

const FALLBACK_IMAGE = 'https://images.unsplash.com/photo-1482731215275-a1f151646268?auto=format&fit=crop&w=1920&q=80';

// Jak silně pozadí „zaostává" za scrollem (0 = statické, 1 = jede se stránkou).
const PARALLAX_FACTOR = 0.4;

export function Hero({ settings, enabledSections }: { settings: SiteSettings; enabledSections: string[] }) {
    const image = settings.hero_image || FALLBACK_IMAGE;
    const hasAbout = Boolean(settings.hero_about);
    const showWrite = enabledSections.includes('inquiry');
    // Volitelný „druhý odkaz" (nastavení hero_link) - jen když je zvolená sekce zapnutá.
    const secondaryLink =
        settings.hero_link === 'portfolio' && enabledSections.includes('portfolio')
            ? { href: '#ukazky', label: '…ukázky mojí práce' }
            : settings.hero_link === 'instagram' && enabledSections.includes('instagram')
              ? { href: '#instagram', label: '…mrkněte na můj Instagram' }
              : null;
    const secondaryLinkClass =
        'text-sm font-medium text-white/90 underline underline-offset-4 transition-colors hover:text-white';
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
            {/* Scrim: základní tmavý přechod + hodně rozlité radiální ztmavení za textem
                (velké, aby nevzniklo viditelné halo), kvůli čitelnosti nad rušnou fotkou. */}
            <div
                className="absolute inset-0"
                style={{
                    background:
                        'linear-gradient(to bottom, rgba(0,0,0,0.58), rgba(0,0,0,0.48) 50%, rgba(0,0,0,0.66)), radial-gradient(135% 100% at 50% 42%, rgba(0,0,0,0.3), transparent 60%)',
                }}
            />

            <div className="container relative z-10 text-white">
                <h1 className="animate-fade-up text-center text-4xl font-extrabold leading-[1.05] tracking-tight [text-wrap:balance] sm:text-5xl lg:text-6xl">
                    {settings.hero_title || settings.site_title || 'Rekonstrukce na klíč'}
                </h1>
                <div className={cn('mt-8', hasAbout && 'grid items-center gap-10 lg:grid-cols-2 lg:gap-0')}>
                    <div
                        className={cn(
                            'flex animate-fade-up flex-col items-center text-center',
                            hasAbout && 'lg:items-end lg:pr-14 lg:text-right',
                        )}
                        style={{ animationDelay: '0.1s', opacity: 0 }}
                    >
                        {settings.hero_place && (
                            <span className="text-accent-hero block text-2xl font-bold sm:text-3xl lg:text-4xl">
                                {settings.hero_place}
                            </span>
                        )}
                        <p className="mt-4 max-w-md text-lg text-white/75">
                            {settings.slogan || 'Poctivě a s osobním přístupem, od návrhu po úklid.'}
                        </p>
                        <div
                            className={cn(
                                'mt-8 flex flex-col gap-3',
                                hasAbout ? 'items-center lg:items-end' : 'items-center',
                            )}
                        >
                            {settings.contact_phone ? (
                                <>
                                    <a
                                        href={`tel:${settings.contact_phone.replace(/\s/g, '')}`}
                                        className={cn(buttonVariants({ size: 'lg' }))}
                                    >
                                        <Icon name="phone" className="h-5 w-5" />
                                        Zavolat {settings.contact_phone}
                                    </a>
                                    {showWrite && (
                                        <a href="#poptavka" className={secondaryLinkClass}>
                                            …nebo mi napište
                                        </a>
                                    )}
                                </>
                            ) : (
                                <a href="#poptavka" className={cn(buttonVariants({ size: 'lg' }))}>
                                    Nezávazná poptávka
                                    <Icon name="arrow-right" className="h-5 w-5" />
                                </a>
                            )}
                            {!hasAbout && secondaryLink && (
                                <a href={secondaryLink.href} className={secondaryLinkClass}>
                                    {secondaryLink.label}
                                </a>
                            )}
                        </div>
                    </div>

                    {hasAbout && (
                        <div
                            className="animate-fade-up text-center lg:border-l lg:border-white/15 lg:pl-14 lg:text-left"
                            style={{ animationDelay: '0.15s', opacity: 0 }}
                        >
                            <p className="text-accent-hero text-sm font-semibold uppercase tracking-[0.16em]">
                                Pár slov o mně
                            </p>
                            <p className="mx-auto mt-3 max-w-sm leading-relaxed text-white/80 lg:mx-0">
                                {settings.hero_about}
                            </p>
                            {secondaryLink && (
                                <a href={secondaryLink.href} className={cn(secondaryLinkClass, 'mt-4 inline-block')}>
                                    {secondaryLink.label}
                                </a>
                            )}
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
