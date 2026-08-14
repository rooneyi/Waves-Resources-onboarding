import React, {useEffect, useState} from 'react'
import { getProfile } from '../../lib/api'

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
      <div className="w-full max-w-md bg-white p-8 rounded-lg shadow">
        <h2 className="text-2xl font-bold mb-4">Profile</h2>
        <p className="mb-2"><strong>Name:</strong> {profile.fullName}</p>
        <p className="mb-2"><strong>Email:</strong> {profile.email}</p>
        <p className="mb-2"><strong>Verified:</strong> {profile.emailVerified ? 'Yes' : 'No'}</p>
      </div>
    </div>
  )
}
