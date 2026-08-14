import React from 'react'

export function Input({ label, type = 'text', className = '', ...props }: any) {
  return (
    <div className={`w-full ${label ? 'mb-3' : ''}`}>
      {label ? <div className="mb-1 text-sm font-medium text-slate-700 dark:text-slate-200">{label}</div> : null}
      <input
        type={type}
        className={`w-full rounded-md border border-slate-200 bg-white/80 px-3 py-2 text-sm placeholder:text-slate-400 dark:bg-slate-900 dark:border-slate-800 dark:placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 transition disabled:cursor-not-allowed disabled:opacity-50 ${className}`}
        {...props}
      />
    </div>
  )
}
