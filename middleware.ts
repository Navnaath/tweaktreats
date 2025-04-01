import { NextResponse } from "next/server"
import type { NextRequest } from "next/server"
import { getToken } from "next-auth/jwt"

// This function can be marked `async` if using `await` inside
export async function middleware(request: NextRequest) {
  const { pathname } = request.nextUrl

  // Check if the pathname is a protected route
  const protectedRoutes = ["/dashboard", "/favorites", "/profile", "/settings"]
  const isProtectedRoute = protectedRoutes.some((route) => pathname.startsWith(route))

  // Check if the user is authenticated
  const token = await getToken({ req: request })
  const isAuthenticated = !!token

  // If the route is protected and the user is not authenticated, redirect to login
  if (isProtectedRoute && !isAuthenticated) {
    const url = new URL("/login", request.url)
    url.searchParams.set("callbackUrl", encodeURI(pathname))
    return NextResponse.redirect(url)
  }

  // If the user is authenticated and trying to access login/register, redirect to dashboard
  if (isAuthenticated && (pathname === "/login" || pathname === "/register")) {
    return NextResponse.redirect(new URL("/dashboard", request.url))
  }

  return NextResponse.next()
}

// See "Matching Paths" below to learn more
export const config = {
  matcher: ["/dashboard/:path*", "/favorites/:path*", "/profile/:path*", "/settings/:path*", "/login", "/register"],
}

