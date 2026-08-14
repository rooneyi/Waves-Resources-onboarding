import React from 'react'

export function CardContent({ children, className = '' }: any) {
  return <div className={`px-6 pb-6 ${className}`}>{children}</div>
}

export default CardContent
