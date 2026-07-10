import { useRef, useState } from 'react';
import { Icon } from '@/components/Icon';

// Interaktivní posuvník před/po: „po" je základ, „před" se ořízne přes clip-path
// podle pozice děliče. Ovládání přes pointer události (myš i dotyk).
export function BeforeAfter({ before, after, alt }: { before: string; after: string; alt: string }) {
    const [position, setPosition] = useState(50);
    const containerRef = useRef<HTMLDivElement>(null);
    const dragging = useRef(false);

    const updateFromClientX = (clientX: number) => {
        const element = containerRef.current;
        if (!element) return;
        const rect = element.getBoundingClientRect();
        const percent = ((clientX - rect.left) / rect.width) * 100;
        setPosition(Math.max(0, Math.min(100, percent)));
    };

    const onPointerDown = (event: React.PointerEvent<HTMLDivElement>) => {
        dragging.current = true;
        event.currentTarget.setPointerCapture(event.pointerId);
        updateFromClientX(event.clientX);
    };
    const onPointerMove = (event: React.PointerEvent<HTMLDivElement>) => {
        if (dragging.current) updateFromClientX(event.clientX);
    };
    const stop = () => {
        dragging.current = false;
    };

    return (
        <div
            ref={containerRef}
            onPointerDown={onPointerDown}
            onPointerMove={onPointerMove}
            onPointerUp={stop}
            onPointerCancel={stop}
            className="relative aspect-[4/3] w-full cursor-ew-resize select-none overflow-hidden rounded-lg border border-border"
        >
            <img
                src={after}
                alt={alt}
                loading="lazy"
                draggable={false}
                className="absolute inset-0 h-full w-full object-cover"
            />
            <img
                src={before}
                alt={alt}
                loading="lazy"
                draggable={false}
                style={{ clipPath: `inset(0 ${100 - position}% 0 0)` }}
                className="absolute inset-0 h-full w-full object-cover"
            />

            <span className="pointer-events-none absolute left-3 top-3 rounded-md bg-black/60 px-2 py-0.5 text-xs font-semibold text-white">
                Před
            </span>
            <span className="pointer-events-none absolute right-3 top-3 rounded-md bg-black/60 px-2 py-0.5 text-xs font-semibold text-white">
                Po
            </span>

            <div className="pointer-events-none absolute inset-y-0" style={{ left: `${position}%` }}>
                <div className="absolute inset-y-0 w-0.5 -translate-x-1/2 bg-white/90" />
                <div className="absolute top-1/2 flex h-9 w-9 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full border border-black/10 bg-white text-black/70 shadow-md">
                    <Icon name="unfold-horizontal" className="h-4 w-4" />
                </div>
            </div>
        </div>
    );
}
