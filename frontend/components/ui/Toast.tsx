import React, {useEffect} from 'react'

export function Toast({ message, type = 'info', onClose }: any) {
  useEffect(()=>{
    const id = setTimeout(()=>onClose?.(), 4000)
    return ()=>clearTimeout(id)
  },[onClose])

  const bg = type === 'error' ? 'bg-red-600' : type === 'success' ? 'bg-green-600' : 'bg-slate-800'

  return (
    <div className={`fixed top-6 right-6 z-50 ${bg} text-white px-4 py-2 rounded-md shadow-lg`} role="status">
      {message}
    </div>
  )
}
