import type { VariantProps } from "class-variance-authority"
import { cva } from "class-variance-authority"

export { default as Button } from "./Button.vue"

export const buttonVariants = cva(
  "inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-full text-sm font-semibold transition-[background-color,border-color,color,box-shadow] duration-[160ms] disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg:not([class*='size-'])]:size-4 shrink-0 [&_svg]:shrink-0 outline-none focus-visible:ring-[3px] focus-visible:ring-ring/60 focus-visible:ring-offset-2 focus-visible:ring-offset-background aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive min-h-11",
  {
    variants: {
      variant: {
        default:
          "bg-primary text-primary-foreground hover:bg-[var(--brand-strong)]",
        destructive:
          "bg-destructive text-destructive-foreground hover:bg-destructive/90 focus-visible:ring-destructive/20 dark:focus-visible:ring-destructive/40",
        outline:
          "border border-primary bg-transparent text-primary shadow-xs hover:bg-brand-soft/80 dark:border-primary dark:hover:bg-primary/10",
        secondary:
          "bg-secondary text-secondary-foreground hover:bg-secondary/80",
        ghost:
          "text-foreground hover:bg-muted hover:text-foreground dark:hover:bg-muted/80",
        ceremonial:
          "border border-gold bg-transparent text-gold hover:bg-gold-soft/30 dark:text-gold-light dark:border-gold-light",
        link: "rounded-none min-h-0 text-primary underline-offset-4 hover:underline",
      },
      size: {
        "default": "h-11 px-5 py-2 has-[>svg]:px-4",
        "sm": "h-9 gap-1.5 px-4 text-xs has-[>svg]:px-3",
        "lg": "h-12 px-7 text-base has-[>svg]:px-5",
        "icon": "size-11",
        "icon-sm": "size-9",
        "icon-lg": "size-12",
      },
    },
    defaultVariants: {
      variant: "default",
      size: "default",
    },
  },
)
export type ButtonVariants = VariantProps<typeof buttonVariants>
