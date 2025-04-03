"use client";
import { useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/contexts/AuthContext';
import styles from '@/styles/Dashboard.module.css';
import { DashboardTabs } from '@/components/dashboard/DashboardTabs';

export default function DashboardPage() {
  const router = useRouter();
  const { user, isAuthenticated } = useAuth();

  useEffect(() => {
    if (!isAuthenticated || user?.user_type !== 'enterprise') {
      router.push('/');
    }
  }, [isAuthenticated, user, router]);

  if (!isAuthenticated || user?.user_type !== 'enterprise') {
    return null;
  }

  return (
    <div className={styles.container}>
      <h1 className={styles.title}>Enterprise Dashboard</h1>
      <DashboardTabs />
    </div>
  );
}
