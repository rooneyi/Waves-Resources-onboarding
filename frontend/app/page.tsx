import Link from 'next/link'
import React from 'react'

export default function Home() {
  return (
    <main className="min-h-screen bg-slate-50 flex items-center justify-center">
      <div className="max-w-xl w-full p-8">
        <h1 className="text-3xl font-extrabold mb-6">Waves Resources — Frontend</h1>
        <p className="mb-6 text-slate-700">Minimal Next.js app with shadcn-inspired UI components.</p>
        <div className="flex gap-3">
          <Link href="/register" className="px-4 py-2 bg-indigo-600 text-white rounded-md">Register</Link>
          <Link href="/login" className="px-4 py-2 bg-slate-800 text-white rounded-md">Login</Link>
          <Link href="/me" className="px-4 py-2 bg-green-600 text-white rounded-md">Profile</Link>
        </div>
      </div>
    </main>
  )
}
