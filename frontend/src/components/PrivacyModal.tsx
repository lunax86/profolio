import { useEffect } from 'react';
import { Icon } from '@/components/Icon';

interface PrivacyModalProps {
    open: boolean;
    onClose: () => void;
    text?: string;
}

/** Lehký modál se zásadami ochrany osobních údajů (bez externí závislosti). */
export function PrivacyModal({ open, onClose, text }: PrivacyModalProps) {
    useEffect(() => {
        if (!open) return;
        const onKeyDown = (event: KeyboardEvent) => {
            if (event.key === 'Escape') onClose();
        };
        window.addEventListener('keydown', onKeyDown);
        document.body.style.overflow = 'hidden';
        return () => {
            window.removeEventListener('keydown', onKeyDown);
            document.body.style.overflow = '';
        };
    }, [open, onClose]);

    if (!open) return null;

    return (
        <div
            className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            onClick={onClose}
            role="dialog"
            aria-modal="true"
            aria-label="Zásady ochrany osobních údajů"
        >
            <div
                className="relative max-h-[85vh] w-full max-w-2xl overflow-y-auto rounded-lg border border-border bg-card p-6 shadow-xl sm:p-8"
                onClick={(event) => event.stopPropagation()}
            >
                <button
                    type="button"
                    onClick={onClose}
                    aria-label="Zavřít"
                    className="absolute right-4 top-4 text-muted-foreground transition-colors hover:text-foreground"
                >
                    <Icon name="x" className="h-5 w-5" />
                </button>
                {text ? (
                    <div className="whitespace-pre-line text-sm leading-relaxed text-foreground">{text}</div>
                ) : (
                    <p className="text-sm text-muted-foreground">
                        Zásady ochrany osobních údajů zatím nebyly vyplněny.
                    </p>
                )}
            </div>
        </div>
    );
}
