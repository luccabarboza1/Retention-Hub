import fs from 'fs'
import path from 'path'

export async function GET(request: Request) {
  const url = new URL(request.url)
  const data = Object.fromEntries(url.searchParams.entries())
  if (Object.keys(data).length > 0) {
    fs.writeFileSync(
      path.join(process.cwd(), 'debug-heights.json'),
      JSON.stringify(data, null, 2)
    )
  }
  return Response.json({ ok: true, timestamp: new Date().toISOString() })
}
