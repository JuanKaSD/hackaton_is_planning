"use client";
import { createContext, useContext, useState, useEffect } from 'react';
import { usePathname } from 'next/navigation';
import api from '@/api/axios';

interface Airline {
  id: string;
  name: string;
}

interface AirlineContextType {
  airlines: Airline[];
  addAirline: (airline: { name: string }) => Promise<void>;
  updateAirline: (id: string, data: { name: string }) => Promise<void>;
  deleteAirline: (id: string) => Promise<void>;
  loading: boolean;
  error: string | null;
  fetchAirlines: () => Promise<void>;
}

const AirlineContext = createContext<AirlineContextType>({} as AirlineContextType);

export function AirlineProvider({ children }: { children: React.ReactNode }) {
  const [airlines, setAirlines] = useState<Airline[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const pathname = usePathname();

  useEffect(() => {
    if (pathname?.startsWith('/dashboard')) {
      fetchAirlines();
    }
  }, [pathname]);

  const fetchAirlines = async () => {
    setLoading(true);
    try {
      const response = await api.get('/airlines');
      setAirlines(response.data);
    } catch (err) {
      setError('Failed to fetch airlines');
    } finally {
      setLoading(false);
    }
  };

  const addAirline = async (airline: { name: string }) => {
    try {
      const response = await api.post('/airlines', airline);
      console.log('Respuesta del servidor:', response.data);
      
      // Asegurarse de que la respuesta tiene el formato correcto
      const newAirline: Airline = {
        id: response.data.id,
        name: response.data.name
      };
      
      console.log('Nueva aerolínea formateada:', newAirline);
      setAirlines(prevAirlines => [...prevAirlines, newAirline]);
      
      // Verificar el estado actualizado
      console.log('Estado actualizado de airlines:', airlines);
    } catch (err) {
      console.error('Error al añadir aerolínea:', err);
      setError('Failed to add airline');
      throw err;
    }
  };

  const updateAirline = async (id: string, data: { name: string }) => {
    try {
      await api.put(`/airlines/${id}`, data);
      // Refresh airlines list after update instead of updating state directly
      await fetchAirlines();
    } catch (err) {
      setError('Failed to update airline');
      throw err;
    }
  };

  const deleteAirline = async (id: string) => {
    try {
      await api.delete(`/airlines/${id}`);
      setAirlines(airlines.filter(a => a.id !== id));
    } catch (err) {
      setError('Failed to delete airline');
      throw err;
    }
  };

  return (
    <AirlineContext.Provider value={{ 
      airlines, 
      addAirline, 
      updateAirline, 
      deleteAirline,
      loading,
      error,
      fetchAirlines
    }}>
      {children}
    </AirlineContext.Provider>
  );
}

export const useAirlines = () => useContext(AirlineContext);
