"use client"
import React, {useEffect, useState} from 'react'
import { getProfile, logout } from '../../lib/api'
import { useRouter } from 'next/navigation'
import { Card } from '../../components/ui/Card'
import { Avatar } from '../../components/ui/Avatar'

export default function Me() {
  const [profile, setProfile] = useState<any>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(()=>{
    getProfile().then(p=>setProfile(p)).catch(e=>setError(e?.message||'Failed')).finally(()=>setLoading(false))
  },[])

  if (loading) return <div className="min-h-screen flex items-center justify-center">Loading...</div>
  if (error) return <div className="min-h-screen flex items-center justify-center">{error}</div>

  return (
    <div className="min-h-screen flex items-center justify-center bg-slate-50">
      <Card className="w-full max-w-md ring-1 ring-slate-200/60">
        <div className="flex items-center gap-4 mb-4">
          <Avatar src={profile.profileImageUrl || '/placeholder-avatar.png'} size={56} />
          <div>
            <h2 className="text-2xl font-serif font-semibold">{profile.fullName}</h2>
            <p className="text-sm text-slate-500">{profile.email}</p>
          </div>
        </div>
        <p className="mb-2"><strong>Vérifié :</strong> {profile.emailVerified ? 'Oui' : 'Non'}</p>
        <div className="mt-4 flex gap-3">
          <a href="/me/edit" className="px-3 py-2 bg-slate-100 rounded">Modifier</a>
          <button onClick={async ()=>{ await logout(); window.location.href = '/login' }} className="px-3 py-2 bg-red-600 text-white rounded">Se déconnecter</button>
        </div>
      </Card>
    </div>
  )
}
