import { BeforeAfter } from '@/components/BeforeAfter';
import type { PortfolioItem } from '@/lib/api';

export function Portfolio({ items }: { items: PortfolioItem[] }) {
    if (items.length === 0) return null;

    const hasSlider = items.some((item) => item.image_before);

    return (
        <section id="ukazky" className="py-24">
            <div className="container">
                <div className="mx-auto max-w-2xl text-center">
                    <span className="text-sm font-semibold uppercase tracking-wider text-primary">Moje práce</span>
                    <h2 className="mt-3 text-3xl font-bold sm:text-4xl">Ukázky realizací</h2>
                    <p className="mt-4 text-muted-foreground">
                        {hasSlider
                            ? 'Potáhněte fotku a uvidíte proměnu: před a po.'
                            : 'Podívejte se na výběr dokončených zakázek.'}
                    </p>
                </div>

                <div className="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    {items.map((item) => (
                        <figure key={item.id}>
                            {item.image_before ? (
                                <BeforeAfter before={item.image_before} after={item.image_path} alt={item.title} />
                            ) : (
                                <img
                                    src={item.image_path}
                                    alt={item.title}
                                    loading="lazy"
                                    className="aspect-[4/3] w-full rounded-lg border border-border object-cover"
                                />
                            )}
                            {(item.title || item.description) && (
                                <figcaption className="mt-3">
                                    <h3 className="font-semibold">{item.title}</h3>
                                    {item.description && (
                                        <p className="mt-0.5 text-sm text-muted-foreground">{item.description}</p>
                                    )}
                                </figcaption>
                            )}
                        </figure>
                    ))}
                </div>
            </div>
        </section>
    );
}
