import { useEffect, useState } from 'react'
import { Navbar } from './components/Navbar'
import { Hero } from './sections/Hero'
import { Services } from './sections/Services'
import { InquiryForm } from './sections/InquiryForm'
import { Portfolio } from './sections/Portfolio'
import { Footer } from './sections/Footer'
import { api, type PortfolioItem, type Service, type SiteSettings } from './lib/api'

export default function App() {
  const [settings, setSettings] = useState<SiteSettings>({})
  const [services, setServices] = useState<Service[]>([])
  const [portfolio, setPortfolio] = useState<PortfolioItem[]>([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    Promise.all([api.settings(), api.services(), api.portfolio()])
      .then(([s, sv, p]) => {
        setSettings(s)
        setServices(sv)
        setPortfolio(p)
      })
      .catch((e) => console.error('Načtení dat selhalo', e))
      .finally(() => setLoading(false))
  }, [])

  if (loading) {
    return (
      <div className="flex min-h-screen items-center justify-center text-muted-foreground">
        Načítám…
      </div>
    )
  }

  return (
    <>
      <Navbar settings={settings} />
      <main>
        <Hero settings={settings} />
        <Services services={services} />
        <InquiryForm />
        <Portfolio items={portfolio} />
      </main>
      <Footer settings={settings} />
    </>
  )
}
