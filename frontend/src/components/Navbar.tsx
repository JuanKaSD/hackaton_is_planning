"use client";

import { LogOutIcon, MenuIcon } from 'lucide-react';
import Link from "next/link";
import styles from "@/styles/Navbar.module.css";
import { usePathname, useRouter } from "next/navigation";
import { authService } from "@/api/services/auth.service";
import { useAuth } from "@/contexts/AuthContext";
import { useState, useEffect, useRef } from "react";

export default function Navbar() {
  const pathname = usePathname();
  const router = useRouter();
  const { isAuthenticated, setAuth, user } = useAuth();
  const [dropdownOpen, setDropdownOpen] = useState(false);
  const dropdownRef = useRef<HTMLDivElement>(null);

  const handleLogout = () => {
    authService.logout();
    setAuth(false);
    router.push('/');
  };

  const handleClickOutside = (event: MouseEvent) => {
    if (dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
      setDropdownOpen(false);
    }
  };

  useEffect(() => {
    if (dropdownOpen) {
      document.addEventListener("mousedown", handleClickOutside);
    } else {
      document.removeEventListener("mousedown", handleClickOutside);
    }
    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
    };
  }, [dropdownOpen]);

  console.log("User data in Navbar:", user);

  return (
    <nav className={styles.navbar}>
      <div className={styles.container}>
        <Link href="/" className={styles.logo}>
          Hackathon
        </Link>
        <div className={styles.links}>
          {!isAuthenticated ? (
            <div className={styles.dropdown} ref={dropdownRef}>
              <button 
                onClick={() => setDropdownOpen(!dropdownOpen)} 
                className={styles.iconButton}
              >
                <MenuIcon className={styles.iconButton} />
              </button>
              {dropdownOpen && (
                <div className={styles.dropdownMenu}>
                  <Link href="/login" className={pathname === '/login' ? styles.active : ''}>
                    Login
                  </Link>
                  <Link href="/signup" className={pathname === '/signup' ? styles.active : ''}>
                    Sign Up
                  </Link>
                </div>
              )}
            </div>
          ) : (
            <>
              {user && <span>Welcome {user.name}</span>}
              {user?.user_type === 'enterprise' && (
                <Link
                  href="/dashboard"
                  className={pathname === '/dashboard' ? styles.active : ''}
                >
                  Dashboard
                </Link>
              )}
              <Link
                href="/profile"
                className={pathname === '/profile' ? styles.active : ''}
              >
                Profile
              </Link>
              <LogOutIcon onClick={handleLogout} className={styles.logoutButton} />
            </>
          )}
        </div>
      </div>
    </nav>
  );
}
