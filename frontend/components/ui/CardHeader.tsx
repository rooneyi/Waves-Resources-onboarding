import React from 'react'

export function CardHeader({ children, className = '' }: any) {
  return <div className={`px-6 pt-6 ${className}`}>{children}</div>
}

export default CardHeader
