import { useEffect, useState } from 'react';
import { Icon } from './Icon';
import { Button } from './ui/button';
import { useTheme } from '@/lib/theme';
import type { SiteSettings } from '@/lib/api';
import { cn } from '@/lib/utils';

const links = [
    { href: '#sluzby', label: 'Služby' },
    { href: '#poptavka', label: 'Poptávka' },
    { href: '#ukazky', label: 'Reference' },
    { href: '#kontakt', label: 'Kontakt' },
];

export function Navbar({ settings }: { settings: SiteSettings }) {
    const { theme, toggle } = useTheme();
    const [scrolled, setScrolled] = useState(false);
    const [open, setOpen] = useState(false);

    useEffect(() => {
        const onScroll = () => setScrolled(window.scrollY > 20);
        onScroll();
        window.addEventListener('scroll', onScroll);
        return () => window.removeEventListener('scroll', onScroll);
    }, []);

    return (
        <header
            className={cn(
                'fixed inset-x-0 top-0 z-50 transition-all duration-300',
                scrolled ? 'border-b border-border bg-background/85 backdrop-blur' : 'bg-transparent',
            )}
        >
            <div className="container flex h-16 items-center justify-between gap-4">
                <a href="#top" className={cn('text-lg font-bold', !scrolled && 'text-white drop-shadow')}>
                    {settings.site_title ?? 'Vaše firma'}
                </a>

                <nav className="hidden items-center gap-1 md:flex">
                    {links.map((l) => (
                        <a
                            key={l.href}
                            href={l.href}
                            className={cn(
                                'rounded-md px-3 py-2 text-sm font-medium transition-colors hover:bg-secondary',
                                !scrolled && 'text-white/90 hover:bg-white/10',
                            )}
                        >
                            {l.label}
                        </a>
                    ))}
                </nav>

                <div className="flex items-center gap-2">
                    {settings.contact_phone && (
                        <a
                            href={`tel:${settings.contact_phone.replace(/\s/g, '')}`}
                            className={cn(
                                'hidden items-center gap-2 text-sm font-medium sm:flex',
                                !scrolled && 'text-white',
                            )}
                        >
                            <Icon name="phone" className="h-4 w-4" />
                            {settings.contact_phone}
                        </a>
                    )}
                    <Button
                        variant="ghost"
                        size="icon"
                        onClick={toggle}
                        aria-label="Přepnout světlý/tmavý režim"
                        className={cn(!scrolled && 'text-white hover:bg-white/10')}
                    >
                        <Icon name={theme === 'dark' ? 'sun' : 'moon'} className="h-5 w-5" />
                    </Button>
                    <Button
                        variant="ghost"
                        size="icon"
                        className={cn('md:hidden', !scrolled && 'text-white hover:bg-white/10')}
                        onClick={() => setOpen((isOpen) => !isOpen)}
                        aria-label="Menu"
                    >
                        <Icon name={open ? 'x' : 'menu'} className="h-5 w-5" />
                    </Button>
                </div>
            </div>

            {open && (
                <nav className="border-t border-border bg-background md:hidden">
                    <div className="container flex flex-col py-2">
                        {links.map((l) => (
                            <a
                                key={l.href}
                                href={l.href}
                                onClick={() => setOpen(false)}
                                className="rounded-md px-3 py-3 text-sm font-medium hover:bg-secondary"
                            >
                                {l.label}
                            </a>
                        ))}
                    </div>
                </nav>
            )}
        </header>
    );
}
