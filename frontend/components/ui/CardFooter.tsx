import React from 'react'

export function CardFooter({ children, className = '' }: any) {
  return <div className={`px-6 pt-4 pb-6 ${className}`}>{children}</div>
}

export default CardFooter
