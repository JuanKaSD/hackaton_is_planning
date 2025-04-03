"use client";
import { useAuthProtect } from '@/hooks/useAuthProtect';
import styles from "@/styles/Auth.module.css";
import { useState, useEffect } from "react";
import { authService } from "@/api/services/auth.service";
import { useRouter } from "next/navigation";
import { useAuth } from "@/contexts/AuthContext";
import Link from 'next/link';

export default function LoginPage() {
  useAuthProtect();
  const router = useRouter();
  const { setAuth } = useAuth();
  const [formData, setFormData] = useState({
    email: '',
    password: ''
  });
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    authService.setAuthHandler(setAuth);
  }, [setAuth]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      const response = await authService.login(formData);
      console.log({response})
      setAuth(true, response.user);
      router.push('/');
      router.refresh(); 
    } catch (err: any) {
      setError(err.response?.data?.message || 'Failed to login');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className={styles.authContainer}>
      <div className={styles.authCard}>
        <h1>Welcome Back</h1>
        <p className={styles.subtitle}>Continue your journey with us</p>
        <form className={styles.form} onSubmit={handleSubmit}>
          <div className={styles.inputGroup}>
            <label htmlFor="email">Email Address</label>
            <input 
              type="email" 
              id="email" 
              placeholder="your@email.com"
              value={formData.email}
              onChange={(e) => setFormData({...formData, email: e.target.value})}
              required 
            />
          </div>
          <div className={styles.inputGroup}>
            <label htmlFor="password">Password</label>
            <input 
              type="password" 
              id="password" 
              placeholder="Enter your password"
              value={formData.password}
              onChange={(e) => setFormData({...formData, password: e.target.value})}
              required 
            />
          </div>
          <Link className={styles.createAccount} href="/signup">Create an account</Link>
          {error && <span className={styles.errorText}>{error}</span>}
          <button type="submit" className={styles.submitButton} disabled={loading}>
            {loading ? 'Logging in...' : 'Login'}
          </button>
        </form>
      </div>
    </div>
  );
}
