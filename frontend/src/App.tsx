import { useEffect, useState } from 'react';
import { Navbar } from './components/Navbar';
import { Hero } from './sections/Hero';
import { Services } from './sections/Services';
import { InquiryForm } from './sections/InquiryForm';
import { Portfolio } from './sections/Portfolio';
import { Instagram } from './sections/Instagram';
import { Reviews } from './sections/Reviews';
import { Footer } from './sections/Footer';
import { PrivacyModal } from './components/PrivacyModal';
import { api, parseSections, type PortfolioItem, type Service, type SiteSettings, type Testimonial } from './lib/api';

export default function App() {
    const [settings, setSettings] = useState<SiteSettings>({});
    const [services, setServices] = useState<Service[]>([]);
    const [portfolio, setPortfolio] = useState<PortfolioItem[]>([]);
    const [testimonials, setTestimonials] = useState<Testimonial[]>([]);
    const [loading, setLoading] = useState(true);
    const [privacyOpen, setPrivacyOpen] = useState(false);

    useEffect(() => {
        // návštěvnost - jednou za relaci prohlížeče
        if (!sessionStorage.getItem('hit')) {
            sessionStorage.setItem('hit', '1');
            api.hit();
        }
    }, []);

    useEffect(() => {
        Promise.all([api.settings(), api.services(), api.portfolio(), api.testimonials()])
            .then(([loadedSettings, loadedServices, loadedPortfolio, loadedTestimonials]) => {
                setSettings(loadedSettings);
                setServices(loadedServices);
                setPortfolio(loadedPortfolio);
                setTestimonials(loadedTestimonials);
            })
            .catch((error) => console.error('Načtení dat selhalo', error))
            .finally(() => setLoading(false));
    }, []);

    if (loading) {
        return <div className="flex min-h-screen items-center justify-center text-muted-foreground">Načítám…</div>;
    }

    // Hero a Footer jsou fixní; prostřední sekce se řídí nastavením (pořadí + viditelnost).
    const sections = parseSections(settings.sections);
    const enabledSections = sections.filter((section) => section.enabled).map((section) => section.key);

    const renderSection = (key: string) => {
        switch (key) {
            case 'services':
                return <Services key={key} services={services} />;
            case 'inquiry':
                return <InquiryForm key={key} onOpenPrivacy={() => setPrivacyOpen(true)} />;
            case 'portfolio':
                return <Portfolio key={key} items={portfolio} />;
            case 'reviews':
                return <Reviews key={key} testimonials={testimonials} />;
            case 'instagram':
                return <Instagram key={key} settings={settings} />;
            default:
                return null;
        }
    };

    return (
        <>
            <Navbar settings={settings} enabledSections={enabledSections} />
            <main>
                <Hero settings={settings} />
                {enabledSections.map((key) => renderSection(key))}
            </main>
            <Footer settings={settings} onOpenPrivacy={() => setPrivacyOpen(true)} />
            <PrivacyModal open={privacyOpen} onClose={() => setPrivacyOpen(false)} text={settings.privacy_policy} />
        </>
    );
}
