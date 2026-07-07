import type { PortfolioItem } from '@/lib/api';

export function Portfolio({ items }: { items: PortfolioItem[] }) {
    if (items.length === 0) return null;

    return (
        <section id="ukazky" className="py-24">
            <div className="container">
                <div className="mx-auto max-w-2xl text-center">
                    <span className="text-sm font-semibold uppercase tracking-wider text-primary">Naše práce</span>
                    <h2 className="mt-3 text-3xl font-bold sm:text-4xl">Ukázky realizací</h2>
                    <p className="mt-4 text-muted-foreground">
                        Podívejte se na výběr projektů, které jsme dovedli do konce.
                    </p>
                </div>

                <div className="mt-14 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    {items.map((item) => (
                        <figure
                            key={item.id}
                            className="group relative aspect-[4/3] overflow-hidden rounded-lg border border-border"
                        >
                            <img
                                src={item.image_path}
                                alt={item.title}
                                loading="lazy"
                                className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                            />
                            <figcaption className="absolute inset-x-0 bottom-0 translate-y-2 bg-gradient-to-t from-black/80 to-transparent p-5 text-white opacity-0 transition-all duration-300 group-hover:translate-y-0 group-hover:opacity-100">
                                <h3 className="font-semibold">{item.title}</h3>
                                {item.description && <p className="text-sm text-white/80">{item.description}</p>}
                            </figcaption>
                        </figure>
                    ))}
                </div>
            </div>
        </section>
    );
}
