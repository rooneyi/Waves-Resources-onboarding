import React from 'react'

export function Badge({ children, className = '', variant = 'default' }: any) {
  const base = 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium'
  const styles = variant === 'outline' ? 'border border-slate-200 text-slate-700 bg-white' : 'bg-slate-100 text-slate-800'
  return <span className={`${base} ${styles} ${className}`}>{children}</span>
}

export default Badge

