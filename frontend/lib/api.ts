import { getAccessToken, getRefreshToken, setTokens, clearTokens } from './auth'

const BASE = process.env.NEXT_PUBLIC_API_BASE || 'http://localhost:8000'

async function tryRefresh(): Promise<boolean> {
  const refreshToken = getRefreshToken()
  if (!refreshToken) return false

  const res = await fetch(BASE + '/api/v1/auth/refresh', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ refreshToken }),
  })

  if (!res.ok) return false
  const body = await res.json()
  if (body?.accessToken && body?.refreshToken) {
    setTokens(body.accessToken, body.refreshToken)
    return true
  }
  return false
}

async function request(path: string, opts: RequestInit = {}, retry = true): Promise<any> {
  const headers: Record<string,string> = { 'Content-Type': 'application/json', ...(opts.headers as any || {}) }
  const access = getAccessToken()
  if (access) headers['Authorization'] = `Bearer ${access}`

  let res = await fetch(BASE + path, {
    credentials: 'include',
    headers,
    ...opts,
  })

  if (res.status === 401 && retry) {
    const refreshed = await tryRefresh()
    if (refreshed) {
      return request(path, opts, false)
    }
  }

  if (!res.ok) {
    const text = await res.text()
    throw new Error(text || res.statusText)
  }

  if (res.status === 204) return null
  return await res.json()
}

export async function register(payload: { email: string, fullName: string, password: string }) {
  return request('/api/v1/auth/register', { method: 'POST', body: JSON.stringify(payload) })
}

export async function login(payload: { email: string, password: string }) {
  const body = await request('/api/v1/auth/login', { method: 'POST', body: JSON.stringify(payload) })
  if (body?.accessToken && body?.refreshToken) {
    setTokens(body.accessToken, body.refreshToken)
  }
  return body
}

export async function refresh(body: { refreshToken: string }) {
  return request('/api/v1/auth/refresh', { method: 'POST', body: JSON.stringify(body) })
}

export function getProfile() {
  return request('/api/v1/me', { method: 'GET' })
}

export async function logout() {
  try { await request('/api/v1/auth/logout', { method: 'POST' }) } catch (e) { }
  clearTokens()
}

export default { register, login, refresh, getProfile, logout }

export async function updateProfile(payload: { fullName?: string, email?: string }) {
  return request('/api/v1/me', { method: 'PATCH', body: JSON.stringify(payload) })
}

export async function uploadProfileImage(file: File) {
  const fd = new FormData()
  fd.append('image', file)

  const access = getAccessToken()
  const headers: Record<string,string> = {}
  if (access) headers['Authorization'] = `Bearer ${access}`

  const res = await fetch(BASE + '/api/v1/me/profile-image', {
    method: 'POST',
    credentials: 'include',
    headers,
    body: fd,
  })

  if (!res.ok) {
    const text = await res.text()
    throw new Error(text || res.statusText)
  }

  return await res.json()
}

export function listUsers(query: { page?: number, limit?: number, role?: string, verified?: boolean, sort?: string, direction?: string } = {}) {
  const params = new URLSearchParams()
  if (query.page) params.set('page', String(query.page))
  if (query.limit) params.set('limit', String(query.limit))
  if (query.role) params.set('role', query.role)
  if (typeof query.verified === 'boolean') params.set('verified', String(query.verified))
  if (query.sort) params.set('sort', query.sort)
  if (query.direction) params.set('direction', query.direction)

  const qs = params.toString() ? `?${params.toString()}` : ''
  return request(`/api/v1/admin/users${qs}`, { method: 'GET' })
}

export async function verifyEmail(body: { token: string }) {
  return request('/api/v1/auth/verify-email', { method: 'POST', body: JSON.stringify(body) })
}
