"use client"
import React, {useState} from 'react'
import { verifyEmail } from '../../lib/api'
import { Input } from '../../components/ui/Input'
import { Button } from '../../components/ui/Button'
import { Toast } from '../../components/ui/Toast'
import { useRouter } from 'next/navigation'

export default function VerifyEmail() {
  const [token, setToken] = useState('')
  const [loading, setLoading] = useState(false)
  const [message, setMessage] = useState<string | null>(null)
  const router = useRouter()

  async function onSubmit(e: React.FormEvent) {
    e.preventDefault()
    setLoading(true)
    try {
      await verifyEmail({ token })
      setMessage('Email verified. Redirecting to login...')
      setTimeout(()=>router.push('/login'), 1000)
    } catch (err: any) {
      setMessage(err?.message || 'Verification failed')
    } finally { setLoading(false) }
  }

  return (
    <div className="min-h-screen flex items-center justify-center bg-slate-50">
      <form onSubmit={onSubmit} className="w-full max-w-md">
        <div className="bg-white rounded-lg shadow p-6 ring-1 ring-slate-200/60">
          <h2 className="text-2xl font-serif font-semibold mb-4">Vérification d'email</h2>
          <Input label="Token" value={token} onChange={(e: React.ChangeEvent<HTMLInputElement>)=>setToken(e.target.value)} />
          <div className="mt-4"><Button type="submit" disabled={loading}>{loading ? 'Vérification...' : 'Vérifier'}</Button></div>
        </div>
        {message && <Toast message={message} type={message.includes('failed') ? 'error' : 'success'} onClose={()=>setMessage(null)} />}
      </form>
    </div>
  )
}
