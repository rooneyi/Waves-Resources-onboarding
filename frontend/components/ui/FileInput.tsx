import React from 'react'

export function FileInput({ label, onChange, accept }: any) {
  return (
    <label className="block mb-3">
      <div className="mb-1 text-sm font-medium text-slate-700">{label}</div>
      <input type="file" accept={accept} onChange={onChange} className="w-full" />
    </label>
  )
}
