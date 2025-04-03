import { createContext, useContext, useState, useEffect } from 'react';
import api from '../api/axios';

interface Airport {
  id: string;
  name: string;
  country: string;
  created_at: string;
  updated_at: string;
}

interface AirportContextType {
  airports: Airport[];
  loading: boolean;
}

const AirportContext = createContext<AirportContextType>({
  airports: [],
  loading: false
});

export function AirportProvider({ children }) {
  const [airports, setAirports] = useState<Airport[]>([]);
  const [loading, setLoading] = useState(true);

  const fetchAirports = async () => {
    try {
      const response = await api.get('/airports');
      setAirports(response.data);
    } catch (error) {
      console.error('Error fetching airports:', error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchAirports();
  }, []);

  return (
    <AirportContext.Provider value={{ airports, loading }}>
      {children}
    </AirportContext.Provider>
  );
}

export const useAirports = () => useContext(AirportContext);
