import Link from 'next/link'
import React from 'react'

export function Header() {
  return (
    <header className="w-full bg-white shadow-sm">
      <div className="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
        <Link href="/" className="font-bold">Waves</Link>
        <nav className="flex gap-3">
          <Link href="/register" className="text-sm text-slate-700">Register</Link>
          <Link href="/login" className="text-sm text-slate-700">Login</Link>
          <Link href="/me" className="text-sm text-slate-700">Profile</Link>
          <Link href="/admin/users" className="text-sm text-slate-700">Admin</Link>
        </nav>
      </div>
    </header>
  )
}
