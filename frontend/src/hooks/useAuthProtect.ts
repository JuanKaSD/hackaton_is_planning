import { useEffect } from 'react';
import { useRouter } from 'next/navigation';

export function useAuthProtect(redirectIfAuth: boolean = true) {
  const router = useRouter();

  useEffect(() => {
    const token = localStorage.getItem('token');
    if (token && redirectIfAuth) {
      router.replace('/');
    }
  }, [router, redirectIfAuth]);
}
