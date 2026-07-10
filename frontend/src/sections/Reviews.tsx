import { Icon } from '@/components/Icon';
import type { Testimonial } from '@/lib/api';

// Recenze zákazníků: sociální důkaz. Vykreslí se jen když nějaká recenze existuje.
export function Reviews({ testimonials }: { testimonials: Testimonial[] }) {
    if (testimonials.length === 0) return null;

    return (
        <section id="recenze" className="py-24">
            <div className="container">
                <div className="mx-auto max-w-2xl text-center">
                    <span className="text-sm font-semibold uppercase tracking-wider text-primary">
                        Co říkají zákazníci
                    </span>
                    <h2 className="mt-3 text-3xl font-bold sm:text-4xl">Recenze</h2>
                    <p className="mt-4 text-muted-foreground">Nejlepší vizitka je spokojený zákazník.</p>
                </div>

                <div className="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    {testimonials.map((testimonial) => (
                        <figure
                            key={testimonial.id}
                            className="flex flex-col rounded-lg border border-border bg-card p-6"
                        >
                            <Icon name="quote" className="h-8 w-8 text-primary/30" />
                            <blockquote className="mt-3 flex-1 whitespace-pre-line text-muted-foreground">
                                {testimonial.text}
                            </blockquote>
                            <figcaption className="mt-5">
                                <div className="font-semibold">{testimonial.author}</div>
                                {testimonial.role && (
                                    <div className="text-sm text-muted-foreground">{testimonial.role}</div>
                                )}
                            </figcaption>
                        </figure>
                    ))}
                </div>
            </div>
        </section>
    );
}
