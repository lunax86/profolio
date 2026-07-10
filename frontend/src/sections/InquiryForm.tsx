import { useRef, useState } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { Icon } from '@/components/Icon';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { api } from '@/lib/api';

const schema = z.object({
    name: z.string().min(2, 'Zadejte jméno'),
    email: z.string().email('Neplatný e-mail'),
    phone: z.string().optional(),
    message: z.string().optional(),
    // honeypot - skryté pole; člověk ho nechá prázdné, bot ho vyplní
    website: z.string().optional(),
});

type FormValues = z.infer<typeof schema>;

export function InquiryForm({ onOpenPrivacy }: { onOpenPrivacy: () => void }) {
    const [sent, setSent] = useState(false);
    const [serverError, setServerError] = useState<string | null>(null);
    const mountedAt = useRef(Date.now());
    const {
        register,
        handleSubmit,
        reset,
        formState: { errors, isSubmitting },
    } = useForm<FormValues>({ resolver: zodResolver(schema) });

    const onSubmit = async (values: FormValues) => {
        setServerError(null);
        try {
            await api.sendInquiry({
                ...values,
                elapsed: (Date.now() - mountedAt.current) / 1000,
            });
            setSent(true);
            reset();
        } catch (error) {
            setServerError(error instanceof Error ? error.message : 'Odeslání selhalo');
        }
    };

    return (
        <section id="poptavka" className="py-24">
            <div className="container grid gap-12 lg:grid-cols-2">
                <div>
                    <span className="text-sm font-semibold uppercase tracking-wider text-primary">Máte zájem?</span>
                    <h2 className="mt-3 text-3xl font-bold sm:text-4xl">Nezávazná poptávka</h2>
                    <p className="mt-4 text-muted-foreground">
                        Napište nám, co potřebujete. Ozveme se vám zpravidla do 24 hodin s návrhem řešení. Poptávka je
                        zdarma a nezávazná.
                    </p>
                    <ul className="mt-8 space-y-3 text-sm">
                        {['Odpověď do 24 hodin', 'Návrh na míru zdarma', 'Bez skrytých poplatků'].map((benefit) => (
                            <li key={benefit} className="flex items-center gap-3">
                                <Icon name="check-circle-2" className="h-5 w-5 text-primary" />
                                {benefit}
                            </li>
                        ))}
                    </ul>
                </div>

                <div className="rounded-lg border border-border bg-card p-5 shadow-sm sm:p-8">
                    {sent ? (
                        <div className="flex flex-col items-center justify-center gap-4 py-12 text-center">
                            <Icon name="party-popper" className="h-12 w-12 text-primary" />
                            <h3 className="text-xl font-semibold">Děkujeme!</h3>
                            <p className="text-muted-foreground">Vaši poptávku jsme přijali a brzy se ozveme.</p>
                            <Button variant="outline" onClick={() => setSent(false)}>
                                Odeslat další
                            </Button>
                        </div>
                    ) : (
                        <form onSubmit={handleSubmit(onSubmit)} className="space-y-4" noValidate>
                            {/* honeypot - neviditelné pole; člověk ho nevidí, bot ho vyplní */}
                            <div aria-hidden="true" className="absolute left-[-9999px] h-0 w-0 overflow-hidden">
                                <label htmlFor="website">Nevyplňujte</label>
                                <input
                                    id="website"
                                    type="text"
                                    tabIndex={-1}
                                    autoComplete="off"
                                    {...register('website')}
                                />
                            </div>
                            <div>
                                <label className="mb-1.5 block text-sm font-medium">Jméno *</label>
                                <Input {...register('name')} placeholder="Jan Novák" />
                                {errors.name && <p className="mt-1 text-xs text-red-500">{errors.name.message}</p>}
                            </div>
                            <div>
                                <label className="mb-1.5 block text-sm font-medium">E-mail *</label>
                                <Input type="email" {...register('email')} placeholder="jan@example.com" />
                                {errors.email && <p className="mt-1 text-xs text-red-500">{errors.email.message}</p>}
                            </div>
                            <div>
                                <label className="mb-1.5 block text-sm font-medium">Telefon</label>
                                <Input {...register('phone')} placeholder="+420 777 123 456" />
                            </div>
                            <div>
                                <label className="mb-1.5 block text-sm font-medium">Zpráva</label>
                                <Textarea {...register('message')} placeholder="Popište, s čím vám můžeme pomoci…" />
                            </div>
                            {serverError && <p className="text-sm text-red-500">{serverError}</p>}
                            <Button type="submit" className="w-full" disabled={isSubmitting}>
                                {isSubmitting ? 'Odesílám…' : 'Odeslat poptávku'}
                                <Icon name="send" className="h-4 w-4" />
                            </Button>
                            <p className="text-xs text-muted-foreground">
                                Odesláním formuláře souhlasíte se zpracováním osobních údajů za účelem vyřízení
                                poptávky. Více v{' '}
                                <button
                                    type="button"
                                    onClick={onOpenPrivacy}
                                    className="underline underline-offset-2 hover:text-foreground"
                                >
                                    Zásadách ochrany osobních údajů
                                </button>
                                .
                            </p>
                        </form>
                    )}
                </div>
            </div>
        </section>
    );
}
