const ACCESS_KEY = 'waves:accessToken'
const REFRESH_KEY = 'waves:refreshToken'

export function setTokens(accessToken: string, refreshToken: string) {
  try {
    localStorage.setItem(ACCESS_KEY, accessToken)
    localStorage.setItem(REFRESH_KEY, refreshToken)
  } catch (e) {
    // ignore
  }
}

export function getAccessToken(): string | null {
  try { return localStorage.getItem(ACCESS_KEY) } catch (e) { return null }
}

export function getRefreshToken(): string | null {
  try { return localStorage.getItem(REFRESH_KEY) } catch (e) { return null }
}

export function clearTokens() {
  try { localStorage.removeItem(ACCESS_KEY); localStorage.removeItem(REFRESH_KEY) } catch (e) { }
}

export default { setTokens, getAccessToken, getRefreshToken, clearTokens }
