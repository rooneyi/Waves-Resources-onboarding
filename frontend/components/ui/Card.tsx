import React from 'react'

import { CardHeader } from './CardHeader'
import { CardContent } from './CardContent'
import { CardTitle } from './CardTitle'
import { CardDescription } from './CardDescription'
import CardFooter from './CardFooter'

export function Card({ children, className = '' }: any) {
  return (
    <div className={`bg-white rounded-lg shadow p-0 ${className}`}>
      {children}
    </div>
  )
}

Card.Header = CardHeader
Card.Content = CardContent
Card.Title = CardTitle
Card.Description = CardDescription
Card.Footer = CardFooter

export { CardHeader, CardContent, CardTitle, CardDescription, CardFooter }

export default Card
