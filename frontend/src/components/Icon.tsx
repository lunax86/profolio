import { Icon as Iconify } from '@iconify/react'

interface IconProps {
  /** Název lucide ikony bez prefixu, např. "hammer", "wrench". */
  name: string
  className?: string
}

/** Obal nad Iconify se sadou lucide. */
export function Icon({ name, className }: IconProps) {
  return <Iconify icon={`lucide:${name}`} className={className} />
}
