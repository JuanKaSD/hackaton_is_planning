"use client";
import { Geist, Geist_Mono } from "next/font/google";
import "./globals.css";
import Navbar from "@/components/Navbar";
import { AuthProvider } from "@/contexts/AuthContext";
import { AirlineProvider } from '@/contexts/AirlineContext';
import { FlightProvider } from '@/contexts/FlightContext';

const geistSans = Geist({
  variable: "--font-geist-sans",
  subsets: ["latin"],
});

const geistMono = Geist_Mono({
  variable: "--font-geist-mono",
  subsets: ["latin"],
});

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="en">
      <body className={`${geistSans.variable} ${geistMono.variable}`}>
        <AuthProvider>
          <AirlineProvider>
            <FlightProvider>
              <Navbar />
              <main>
                {children}
              </main>
            </FlightProvider>
          </AirlineProvider>
        </AuthProvider>
      </body>
    </html>
  );
}
