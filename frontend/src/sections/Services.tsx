import { Icon } from '@/components/Icon'
import { Card, CardContent } from '@/components/ui/card'
import type { Service } from '@/lib/api'

export function Services({ services }: { services: Service[] }) {
  return (
    <section id="sluzby" className="py-24">
      <div className="container">
        <div className="mx-auto max-w-2xl text-center">
          <span className="text-sm font-semibold uppercase tracking-wider text-primary">
            Co nabízíme
          </span>
          <h2 className="mt-3 text-3xl font-bold sm:text-4xl">Naše služby</h2>
          <p className="mt-4 text-muted-foreground">
            Postaráme se o celý proces od prvního nápadu až po finální předání.
          </p>
        </div>

        <div className="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
          {services.map((service) => (
            <Card key={service.id} className="group">
              <CardContent className="flex flex-col items-start gap-4">
                <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary transition-colors group-hover:bg-primary group-hover:text-primary-foreground">
                  <Icon name={service.icon || 'sparkles'} className="h-6 w-6" />
                </div>
                <h3 className="text-lg font-semibold">{service.title}</h3>
                <p className="text-sm text-muted-foreground">{service.description}</p>
              </CardContent>
            </Card>
          ))}
        </div>
      </div>
    </section>
  )
}
