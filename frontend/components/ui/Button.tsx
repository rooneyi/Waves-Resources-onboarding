import React from 'react'

export function Button({ children, className = '', ...props }: any) {
  return (
    <button
      {...props}
      className={`inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 bg-indigo-600 text-white hover:bg-indigo-500 disabled:opacity-60 ${className}`}
    >
      {children}
    </button>
  )
}
