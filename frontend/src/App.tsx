import { useEffect, useState } from 'react';
import { Navbar } from './components/Navbar';
import { Hero } from './sections/Hero';
import { Services } from './sections/Services';
import { InquiryForm } from './sections/InquiryForm';
import { Portfolio } from './sections/Portfolio';
import { Footer } from './sections/Footer';
import { PrivacyModal } from './components/PrivacyModal';
import { api, type PortfolioItem, type Service, type SiteSettings } from './lib/api';

export default function App() {
    const [settings, setSettings] = useState<SiteSettings>({});
    const [services, setServices] = useState<Service[]>([]);
    const [portfolio, setPortfolio] = useState<PortfolioItem[]>([]);
    const [loading, setLoading] = useState(true);
    const [privacyOpen, setPrivacyOpen] = useState(false);

    useEffect(() => {
        // návštěvnost – jednou za relaci prohlížeče
        if (!sessionStorage.getItem('hit')) {
            sessionStorage.setItem('hit', '1');
            api.hit();
        }
    }, []);

    useEffect(() => {
        Promise.all([api.settings(), api.services(), api.portfolio()])
            .then(([loadedSettings, loadedServices, loadedPortfolio]) => {
                setSettings(loadedSettings);
                setServices(loadedServices);
                setPortfolio(loadedPortfolio);
            })
            .catch((error) => console.error('Načtení dat selhalo', error))
            .finally(() => setLoading(false));
    }, []);

    if (loading) {
        return <div className="flex min-h-screen items-center justify-center text-muted-foreground">Načítám…</div>;
    }

    return (
        <>
            <Navbar settings={settings} />
            <main>
                <Hero settings={settings} />
                <Services services={services} />
                <InquiryForm onOpenPrivacy={() => setPrivacyOpen(true)} />
                <Portfolio items={portfolio} />
            </main>
            <Footer settings={settings} onOpenPrivacy={() => setPrivacyOpen(true)} />
            <PrivacyModal open={privacyOpen} onClose={() => setPrivacyOpen(false)} text={settings.privacy_policy} />
        </>
    );
}
