"use client";
import { useAuthProtect } from '@/hooks/useAuthProtect';
import styles from "@/styles/Auth.module.css";
import { useState, useEffect } from "react";
import { authService } from "@/api/services/auth.service";
import { useRouter } from "next/navigation";
import { useAuth } from "@/contexts/AuthContext";

export default function SignupPage() {
  useAuthProtect();
  const router = useRouter();
  const { setAuth } = useAuth();
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    phone: '',
    password: '',
    confirmPassword: ''
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
      const response = await authService.signup({
        name: formData.name,
        email: formData.email,
        phone: formData.phone,
        password: formData.password,
        password_confirmation: formData.confirmPassword
      });
      setAuth(true, response.user);
      router.push('/');
      router.refresh(); // This will force a refresh of server components
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
          <button type="submit" className={styles.submitButton} disabled={loading}>
            {loading ? 'Creating Account...' : 'Create Account'}
          </button>
        </form>
      </div>
    </div>
  );
}
