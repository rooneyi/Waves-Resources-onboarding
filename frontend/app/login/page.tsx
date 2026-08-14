"use client"
import React, {useState} from 'react'
import { Button } from '../../components/ui/Button'
import { Input } from '../../components/ui/Input'
import { login } from '../../lib/api'
import { useRouter } from 'next/navigation'
import { Toast } from '../../components/ui/Toast'
import { Card } from '../../components/ui/Card'

export default function Login() {
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const router = useRouter()

  async function onSubmit(e: React.FormEvent) {
    e.preventDefault()
    setLoading(true)
    setError(null)
    try {
      if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) { setError('Invalid email'); setLoading(false); return }
      if (!password) { setError('Password is required'); setLoading(false); return }

      await login({ email, password })
      router.push('/me')
    } catch (err: any) {
      setError(err?.message || 'Login failed')
    } finally { setLoading(false) }
  }

  return (
    <div className="min-h-screen flex items-center justify-center bg-slate-50">
      <form onSubmit={onSubmit} className="w-full max-w-md">
        <Card>
          <h2 className="text-2xl font-bold mb-4">Sign in</h2>
          <Input label="Email" type="email" value={email} onChange={e=>setEmail(e.target.value)} />
          <Input label="Password" type="password" value={password} onChange={e=>setPassword(e.target.value)} />
          <div className="mt-4">
            <Button type="submit" disabled={loading}>{loading ? 'Signing in...' : 'Sign in'}</Button>
          </div>
        </Card>
        {error && <Toast message={error} type="error" onClose={()=>setError(null)} />}
      </form>
    </div>
  )
}
