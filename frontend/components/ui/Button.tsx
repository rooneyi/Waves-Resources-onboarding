import React from 'react'

function classNames(...classes: Array<string | false | null | undefined>) {
  return classes.filter(Boolean).join(' ')
}

export function Button({ children, className = '', asChild = false, variant = 'default', size = 'md', ...props }: any) {
  const variantClass = variant === 'outline' ? 'bg-white border border-slate-200 text-slate-800' : variant === 'secondary' ? 'bg-slate-100 text-slate-800' : 'bg-indigo-600 text-white'
  const sizeClass = size === 'lg' ? 'px-5 py-3 text-sm' : 'px-4 py-2 text-sm'
  const base = 'inline-flex items-center justify-center rounded-md font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-60'

  const merged = classNames(base, variantClass, sizeClass, className)

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
