'use client'
import React, {useEffect, useState} from 'react'
import { getProfile, updateProfile, uploadProfileImage } from '../../../lib/api'
import { Input } from '../../../components/ui/Input'
import { Button } from '../../../components/ui/Button'
import { FileInput } from '../../../components/ui/FileInput'
import { useRouter } from 'next/navigation'
import { Card } from '../../../components/ui/Card'

export default function EditProfile() {
  const [profile, setProfile] = useState<any>(null)
  const [fullName, setFullName] = useState('')
  const [email, setEmail] = useState('')
  const [file, setFile] = useState<File | null>(null)
  const [loading, setLoading] = useState(false)
  const router = useRouter()

  useEffect(()=>{
    getProfile().then(p=>{
      setProfile(p)
      setFullName(p.fullName || '')
      setEmail(p.email || '')
    }).catch(()=>{})
  },[])

  async function onSubmit(e: React.FormEvent) {
    e.preventDefault()
    setLoading(true)
    try {
      await updateProfile({ fullName })
      if (file) {
        await uploadProfileImage(file)
      }
      router.push('/me')
    } catch (err: any) {
      alert(err?.message || 'Update failed')
    } finally { setLoading(false) }
  }

  return (
    <div className="min-h-screen flex items-center justify-center bg-slate-50">
      <form onSubmit={onSubmit} className="w-full max-w-md">
        <Card className="ring-1 ring-slate-200/60">
          <h2 className="text-2xl font-serif font-semibold mb-4">Modifier le profil</h2>
          <Input label="Full name" value={fullName} onChange={(e: React.ChangeEvent<HTMLInputElement>) => setFullName(e.target.value)} />
          <Input label="Email" type="email" value={email} onChange={(e: React.ChangeEvent<HTMLInputElement>) => setEmail(e.target.value)} />
          <FileInput label="Profile image" accept="image/*" onChange={(e: React.ChangeEvent<HTMLInputElement>)=>setFile(e.target.files?.[0]||null)} />
          <div className="mt-4">
            <Button type="submit" disabled={loading}>{loading ? 'Saving...' : 'Save'}</Button>
          </div>
        </Card>
      </form>
    </div>
  )
}
