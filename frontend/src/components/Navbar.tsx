"use client";

import { MenuIcon } from 'lucide-react';
import Link from "next/link";
import styles from "@/styles/Navbar.module.css";
import { usePathname, useRouter } from "next/navigation";
import { authService } from "@/api/services/auth.service";
import { useAuth } from "@/contexts/AuthContext";
import { useState } from "react";


export default function Navbar() {
  const pathname = usePathname();
  const router = useRouter();
  const { isAuthenticated, setAuth } = useAuth();
  const [dropdownOpen, setDropdownOpen] = useState(false);

  const handleLogout = () => {
    authService.logout();
    setAuth(false);
    router.push('/');
  };

  return (
    <nav className={styles.navbar}>
      <div className={styles.container}>
        <Link href="/" className={styles.logo}>
          Hackathon
        </Link>
        <div className={styles.links}>
          {!isAuthenticated ? (
            <div className={styles.dropdown}>
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
            <button onClick={handleLogout} className={styles.logoutButton}>
              Logout
            </button>
          )}
        </div>
      </div>
    </nav>
  );
}
