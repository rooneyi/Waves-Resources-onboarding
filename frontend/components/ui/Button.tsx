import React from 'react'

function classNames(...classes: Array<string | false | null | undefined>) {
  return classes.filter(Boolean).join(' ')
}

type ButtonProps = React.ButtonHTMLAttributes<HTMLButtonElement> & {
  asChild?: boolean
  variant?: 'default' | 'outline' | 'secondary' | 'ghost' | 'destructive'
  size?: 'sm' | 'md' | 'lg'
}

export function Button({ children, className = '', asChild = false, variant = 'default', size = 'md', ...props }: ButtonProps) {
  const variants: Record<string, string> = {
    default: 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm',
    outline: 'bg-white border border-slate-200 text-slate-800 hover:bg-slate-50',
    secondary: 'bg-slate-100 text-slate-800 hover:bg-slate-200',
    ghost: 'bg-transparent text-slate-800 hover:bg-slate-100',
    destructive: 'bg-red-600 text-white hover:bg-red-700',
  }

  const sizes: Record<string, string> = {
    sm: 'px-2.5 py-1.5 text-sm',
    md: 'px-4 py-2 text-sm',
    lg: 'px-5 py-3 text-sm',
  }

  const base = 'inline-flex items-center justify-center rounded-md font-semibold transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-indigo-500 disabled:opacity-50 disabled:pointer-events-none'

  const merged = classNames(base, variants[variant] ?? variants.default, sizes[size] ?? sizes.md, className)

  if (asChild && React.isValidElement(children)) {
    return React.cloneElement(children as React.ReactElement, {
      className: classNames((children as any).props.className, merged),
      ...props,
    })
  }

  return (
    <button
      {...props}
      className={merged}
    >
      {children}
    </button>
  )
}
