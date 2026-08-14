"use client"
import React, {useEffect, useState} from 'react'
import { listUsers } from '../../../lib/api'
import { Card } from '../../../components/ui/Card'

export default function AdminUsers() {
  const [users, setUsers] = useState<any[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(()=>{
    listUsers().then((res:any)=>setUsers(res)).catch(e=>setError(e?.message||'Failed')).finally(()=>setLoading(false))
  },[])

  if (loading) return <div className="min-h-screen flex items-center justify-center">Loading...</div>
  if (error) return <div className="min-h-screen flex items-center justify-center">{error}</div>

  return (
    <div className="min-h-screen flex items-center justify-center bg-slate-50">
      <div className="w-full max-w-3xl">
        <Card className="ring-1 ring-slate-200/60">
          <h2 className="text-2xl font-serif font-semibold mb-4">Utilisateurs</h2>
          <table className="w-full table-auto text-left">
            <thead>
              <tr className="border-b">
                <th className="py-2">ID</th>
                <th className="py-2">Full name</th>
                <th className="py-2">Email</th>
                <th className="py-2">Role</th>
                <th className="py-2">Verified</th>
              </tr>
            </thead>
            <tbody>
              {users.map(u=> (
                <tr key={u.id} className="border-b hover:bg-slate-50">
                  <td className="py-2">{u.id}</td>
                  <td className="py-2">{u.fullName}</td>
                  <td className="py-2">{u.email}</td>
                  <td className="py-2">{u.role}</td>
                  <td className="py-2">{u.emailVerified ? 'Oui' : 'Non'}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </Card>
      </div>
    </div>
  )
}
