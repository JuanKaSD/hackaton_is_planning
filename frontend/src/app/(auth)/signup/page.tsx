"use client";
import { useAuthProtect } from '@/hooks/useAuthProtect';
import styles from "@/styles/Auth.module.css";
import { useState, useEffect } from "react";
import { authService } from "@/api/services/auth.service";
import { useRouter } from "next/navigation";
import { useAuth } from "@/contexts/AuthContext";
import Link from 'next/link';

interface SignupFormData {
  name: string;
  email: string;
  phone: string;
  password: string;
  confirmPassword: string;
  user_type: 'client' | 'enterprise';
}

export default function SignupPage() {
  useAuthProtect();
  const router = useRouter();
  const { setAuth } = useAuth();
  const [formData, setFormData] = useState<SignupFormData>({
    name: '',
    email: '',
    phone: '',
    password: '',
    confirmPassword: '',
    user_type: 'client'
  });
  
  const [passwordError, setPasswordError] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    authService.setAuthHandler(setAuth);
  }, [setAuth]);

  const validatePassword = (password: string) => {
    if (password.length < 8) return "Password must be at least 8 characters";
    if (!/[A-Z]/.test(password)) return "Include at least one uppercase letter";
    if (!/[a-z]/.test(password)) return "Include at least one lowercase letter";
    if (!/[0-9]/.test(password)) return "Include at least one number";
    if (!/[^A-Za-z0-9]/.test(password)) return "Include at least one special character";
    return "";
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    const validationError = validatePassword(formData.password);
    if (validationError) {
      setPasswordError(validationError);
      return;
    }
    if (formData.password !== formData.confirmPassword) {
      setPasswordError("Passwords don't match");
      return;
    }

    setError('');
    setLoading(true);
    
    try {
      const signupData = {
        name: formData.name,
        email: formData.email,
        phone: formData.phone,
        password: formData.password,
        password_confirmation: formData.confirmPassword,
        user_type: formData.user_type
      };

      const response = await authService.signup(signupData);
      setAuth(true, response.user);
      router.push('/');
      router.refresh();
    } catch (err: any) {
      setError(err.response?.data?.message || 'Failed to create account');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className={styles.authContainer}>
      <div className={styles.authCard}>
        <h1>Create Account</h1>
        <p className={styles.subtitle}>Start your journey with us today</p>
        <form className={styles.form} onSubmit={handleSubmit}>
          <div className={styles.radioGroup}>
            <div className={styles.radioLabel}>Account Type</div>
            <div className={styles.radioOptions}>
              <label className={`${styles.radioOption} ${formData.user_type === 'client' ? styles.checked : ''}`}>
                <input
                  type="radio"
                  name="user_type"
                  value="client"
                  checked={formData.user_type === 'client'}
                  onChange={(e) => setFormData({...formData, user_type: e.target.value})}
                />
                <span className={styles.radioControl}></span>
                <span className={styles.radioLabel}>Client</span>
              </label>
              <label className={`${styles.radioOption} ${formData.user_type === 'enterprise' ? styles.checked : ''}`}>
                <input
                  type="radio"
                  name="user_type"
                  value="enterprise"
                  checked={formData.user_type === 'enterprise'}
                  onChange={(e) => setFormData({...formData, user_type: e.target.value})}
                />
                <span className={styles.radioControl}></span>
                <span className={styles.radioLabel}>Enterprise</span>
              </label>
            </div>
          </div>
          <div className={styles.inputGroup}>
            <label htmlFor="name">Full Name</label>
            <input 
              type="text" 
              id="name" 
              placeholder="John Doe"
              value={formData.name}
              onChange={(e) => setFormData({...formData, name: e.target.value})}
              required 
            />
          </div>
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
            <label htmlFor="phone">Phone Number</label>
            <input 
              type="tel" 
              id="phone" 
              placeholder="+1234567890"
              value={formData.phone}
              onChange={(e) => setFormData({...formData, phone: e.target.value})}
              required 
            />
          </div>
          <div className={styles.inputGroup}>
            <label htmlFor="password">Password</label>
            <input 
              type="password" 
              id="password" 
              placeholder="Create a strong password"
              value={formData.password}
              onChange={(e) => {
                setFormData({...formData, password: e.target.value});
                setPasswordError(validatePassword(e.target.value));
              }}
              required 
            />
            {passwordError && <span className={styles.errorText}>{passwordError}</span>}
          </div>
          <div className={styles.inputGroup}>
            <label htmlFor="confirmPassword">Confirm Password</label>
            <input 
              type="password" 
              id="confirmPassword" 
              placeholder="Confirm your password"
              value={formData.confirmPassword}
              onChange={(e) => setFormData({...formData, confirmPassword: e.target.value})}
              required 
            />
          </div>
          {error && <span className={styles.errorText}>{error}</span>}
          <Link className={styles.createAccount} href="/login">Already got an account?</Link>
          <button type="submit" className={styles.submitButton} disabled={loading}>
            {loading ? 'Creating Account...' : 'Create Account'}
          </button>
        </form>
      </div>
    </div>
  );
}
