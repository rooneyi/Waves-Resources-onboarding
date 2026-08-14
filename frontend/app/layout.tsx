import React from 'react'
import '../styles/globals.css'
import { Header } from '../components/ui/Header'

export const metadata = {
  title: 'Waves Frontend',
}

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en">
      <body>
        <Header />
        <main>{children}</main>
      </body>
    </html>
  )
}
