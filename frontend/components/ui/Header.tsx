import Link from 'next/link'
import React from 'react'
import { Button } from './Button'

export function Header() {
  return (
    <header className="w-full bg-primary text-white">
      <div className="page-container flex items-center justify-between py-4">
        <Link href="/" className="flex items-center gap-3">
          <div className="w-10 h-10 rounded-md bg-white/10 flex items-center justify-center text-white font-serif text-lg">W</div>
          <div className="leading-tight">
            <div className="font-serif text-lg font-semibold">Waves Resources</div>
            <div className="text-xs opacity-90">Gestion et authentification</div>
          </div>
        </Link>

        <nav className="flex items-center gap-4">
          <Link href="/" className="text-sm text-white/90 hover:underline">Home</Link>
          <Link href="/register" className="text-sm text-white/90 hover:underline">S'inscrire</Link>
          <Link href="/login" className="text-sm text-white/90 hover:underline">Se connecter</Link>
          <Button asChild size="sm" className="ml-3">
            <Link href="/contact" className="px-3">Contact</Link>
          </Button>
        </nav>
      </div>
    </header>
  )
}
