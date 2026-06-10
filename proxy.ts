import { NextRequest, NextResponse } from 'next/server'

const UNPROTECTED_API = ['/api/cron/', '/api/health']

export default function proxy(request: NextRequest) {
  const { pathname } = request.nextUrl

  if (!pathname.startsWith('/api/')) return NextResponse.next()
  if (UNPROTECTED_API.some((p) => pathname.startsWith(p))) return NextResponse.next()

  const token =
    request.headers.get('x-api-token') ??
    request.nextUrl.searchParams.get('api_token') ??
    request.headers.get('authorization')?.replace('Bearer ', '')

  if (!token || token !== process.env.API_ACCESS_TOKEN) {
    return Response.json({ message: 'Unauthorized.' }, { status: 401 })
  }

  return NextResponse.next()
}

export const config = {
  matcher: '/api/:path*',
}
