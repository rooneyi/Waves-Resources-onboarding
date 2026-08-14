import React from 'react'

export function CardTitle({ children, className = '' }: any) {
  return <h3 className={`text-2xl font-bold ${className}`}>{children}</h3>
}

export default CardTitle
