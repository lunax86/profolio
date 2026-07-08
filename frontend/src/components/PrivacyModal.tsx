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
                className="flex max-h-[85dvh] w-full max-w-2xl flex-col overflow-hidden rounded-lg border border-border bg-card shadow-xl"
                onClick={(event) => event.stopPropagation()}
            >
                {/* Hlavička s křížkem zůstává nahoře i při rolování obsahu */}
                <div className="flex shrink-0 justify-end border-b border-border p-3">
                    <button
                        type="button"
                        onClick={onClose}
                        aria-label="Zavřít"
                        className="-m-1 p-1 text-muted-foreground transition-colors hover:text-foreground"
                    >
                        <Icon name="x" className="h-6 w-6" />
                    </button>
                </div>
                <div className="min-h-0 overflow-y-auto p-6 sm:p-8">
                    {text ? (
                        <div className="whitespace-pre-line text-sm leading-relaxed text-foreground">{text}</div>
                    ) : (
                        <p className="text-sm text-muted-foreground">
                            Zásady ochrany osobních údajů zatím nebyly vyplněny.
                        </p>
                    )}
                </div>
            </div>
        </div>
    );
}
