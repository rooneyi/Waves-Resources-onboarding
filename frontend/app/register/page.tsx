import React, {useState} from 'react'
import { Button } from '../../components/ui/Button'
import { Input } from '../../components/ui/Input'
import { register } from '../../lib/api'
import { Toast } from '../../components/ui/Toast'

export default function Register() {
  const [email, setEmail] = useState('')
  const [fullName, setFullName] = useState('')
  const [password, setPassword] = useState('')
  const [loading, setLoading] = useState(false)
  const [message, setMessage] = useState<string | null>(null)

  async function onSubmit(e: React.FormEvent) {
    e.preventDefault()
    setLoading(true)
    setMessage(null)
    // simple client-side validation
    if (!fullName.trim()) { setMessage('Full name is required'); setLoading(false); return }
    if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) { setMessage('Invalid email'); setLoading(false); return }
    if (password.length < 8) { setMessage('Password must be at least 8 characters'); setLoading(false); return }

    try {
      await register({ email, fullName, password })
      setMessage('Registered — check your email to verify.')
    } catch (err: any) {
      setMessage(err?.message || 'Registration failed')
    } finally { setLoading(false) }
  }

  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-b from-slate-50 to-white">
      <form onSubmit={onSubmit} className="w-full max-w-md bg-white p-8 rounded-lg shadow">
        <h2 className="text-2xl font-bold mb-4">Create account</h2>
        <Input label="Full name" value={fullName} onChange={e=>setFullName(e.target.value)} />
        <Input label="Email" type="email" value={email} onChange={e=>setEmail(e.target.value)} />
        <Input label="Password" type="password" value={password} onChange={e=>setPassword(e.target.value)} />
        <div className="mt-4">
          <Button type="submit" disabled={loading}>{loading ? 'Creating...' : 'Create account'}</Button>
        </div>
        {message && <Toast message={message} type={message.includes('failed')||message.includes('Invalid') ? 'error' : 'success'} onClose={()=>setMessage(null)} />}
      </form>
    </div>
  )
}
