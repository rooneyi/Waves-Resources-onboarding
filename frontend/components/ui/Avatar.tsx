import React from 'react'

export function Avatar({ src, alt = 'avatar', size = 40, className = '' }: any) {
  const s = typeof size === 'number' ? `${size}px` : size
  return (
    <img src={src} alt={alt} width={size} height={size} className={`rounded-full object-cover ${className}`} style={{ width: s, height: s }} />
  )
}
