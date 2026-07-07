import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

/** shadcn util - slučuje třídy s korektním přepisem Tailwind. */
export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}
