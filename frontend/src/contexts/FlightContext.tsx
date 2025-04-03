"use client";
import { createContext, useContext, useState, useEffect } from 'react';
import { usePathname } from 'next/navigation';
import api from '@/api/axios';

interface Flight {
  id: string;
  origin: string;
  destination: string;
  duration: number;
  flight_date: string;
  status: string;
  passenger_capacity: number;
}

interface FlightContextType {
  flights: Flight[];
  addFlight: (flight: Omit<Flight, 'id'>) => Promise<void>;
  updateFlight: (id: string, data: Partial<Flight>) => Promise<void>;
  deleteFlight: (id: string) => Promise<void>;
  loading: boolean;
  error: string | null;
  fetchFlights: () => Promise<void>;
}

const FlightContext = createContext<FlightContextType>({} as FlightContextType);

export function FlightProvider({ children }: { children: React.ReactNode }) {
  const [flights, setFlights] = useState<Flight[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const pathname = usePathname();

  useEffect(() => {
    if (pathname?.startsWith('/dashboard')) {
      fetchFlights();
    }
  }, [pathname]);

  const fetchFlights = async () => {
    setLoading(true);
    try {
      const response = await api.get('/flights');
      setFlights(response.data);
    } catch (err) {
      setError('Failed to fetch flights');
    } finally {
      setLoading(false);
    }
  };

  const addFlight = async (flight: Omit<Flight, 'id'>) => {
    try {
      const response = await api.post('/flights', flight);
      setFlights([...flights, response.data]);
    } catch (err) {
      setError('Failed to add flight');
      throw err;
    }
  };

  const updateFlight = async (id: string, data: Partial<Flight>) => {
    try {
      const response = await api.put(`/flights/${id}`, data);
      setFlights(flights.map(f => f.id === id ? response.data : f));
    } catch (err) {
      setError('Failed to update flight');
      throw err;
    }
  };

  const deleteFlight = async (id: string) => {
    try {
      await api.delete(`/flights/${id}`);
      setFlights(flights.filter(f => f.id !== id));
    } catch (err) {
      setError('Failed to delete flight');
      throw err;
    }
  };

  return (
    <FlightContext.Provider value={{ 
      flights, 
      addFlight, 
      updateFlight, 
      deleteFlight,
      loading,
      error,
      fetchFlights
    }}>
      {children}
    </FlightContext.Provider>
  );
}

export const useFlights = () => useContext(FlightContext);
