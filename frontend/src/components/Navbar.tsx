"use client";
import Link from "next/link";
import styles from "@/styles/Navbar.module.css";
import { usePathname, useRouter } from "next/navigation";
import { authService } from "@/api/services/auth.service";
import { useAuth } from "@/contexts/AuthContext";

export default function Navbar() {
  const pathname = usePathname();
  const router = useRouter();
  const { isAuthenticated, setAuth, user } = useAuth();

  const handleLogout = () => {
    authService.logout();
    setAuth(false);
    router.push('/');
  };

  console.log({user})

  return (
    <nav className={styles.navbar}>
      <div className={styles.container}>
        <Link href="/" className={styles.logo}>
          Hackathon
        </Link>
        <div className={styles.links}>
          {isAuthenticated ? (
            <>
              <span className={styles.userName}>
                {user?.name || 'User'}
              </span>
              <Link
                href="/profile"
                className={pathname === '/profile' ? styles.active : ''}
              >
                Profile
              </Link>
              <button onClick={handleLogout} className={styles.logoutButton}>
                Logout
              </button>
            </>
          ) : (
            <>
              <Link
                href="/login"
                className={pathname === '/login' ? styles.active : ''}
              >
                Login
              </Link>
              <Link
                href="/signup"
                className={pathname === '/signup' ? styles.active : ''}
              >
                Sign Up
              </Link>
            </>
          )}
        </div>
      </div>
    </nav>
  );
}
