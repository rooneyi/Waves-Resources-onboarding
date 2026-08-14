import React from 'react'

export function CardDescription({ children, className = '' }: any) {
  return <p className={`text-sm text-slate-600 ${className}`}>{children}</p>
}

export default CardDescription
