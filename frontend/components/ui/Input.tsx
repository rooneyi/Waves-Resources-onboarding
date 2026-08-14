import React from 'react'

export function Input({ label, type = 'text', className = '', ...props }: any) {
  return (
    <label className="block mb-3">
      <div className="mb-1 text-sm font-medium text-slate-700">{label}</div>
      <input
        type={type}
        className={`w-full rounded-md border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-indigo-200 ${className}`}
        {...props}
      />
    </label>
  )
}
