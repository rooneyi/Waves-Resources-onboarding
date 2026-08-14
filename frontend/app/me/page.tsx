import React, {useEffect, useState} from 'react'
import { getProfile } from '../../lib/api'
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
      <Card className="w-full max-w-md">
        <div className="flex items-center gap-4 mb-4">
          <Avatar src={profile.profileImageUrl || '/placeholder-avatar.png'} size={56} />
          <div>
            <h2 className="text-2xl font-bold">{profile.fullName}</h2>
            <p className="text-sm text-slate-500">{profile.email}</p>
          </div>
        </div>
        <p className="mb-2"><strong>Verified:</strong> {profile.emailVerified ? 'Yes' : 'No'}</p>
      </Card>
    </div>
  )
}
