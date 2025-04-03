"use client";
import { createContext, useContext, useState, useEffect, ReactNode } from 'react';
import { usePathname } from 'next/navigation';
import api from '../api/axios';

interface Airport {
  id: string;
  name: string;
  country: string;
  created_at: string;
  updated_at: string;
}

interface Flight {
  id: number;
  airline_id: number;
  origin: string;
  destination: string;
  duration: number;
  flight_date: string;
  status: string;
  passenger_capacity: number;
  created_at: string;
  updated_at: string;
  origin_airport?: Airport;
  destination_airport?: Airport;
}

interface AirlineWithFlights {
  id: number;
  name: string;
  flights: Flight[];
}

interface FlightFormData {
  origin: string;
  destination: string;
  duration: number | string;
  flight_date: string;
  status: string;
  passenger_capacity: number | string;
  airline_id: number | string;
}

interface FlightContextType {
  flights: Flight[];
  addFlight: (flight: FlightFormData) => Promise<void>;
  updateFlight: (id: number, data: Partial<Flight>) => Promise<void>;
  deleteFlight: (id: number) => Promise<void>;
  loading: boolean;
  error: string | null;
  fetchFlights: () => Promise<void>;
}

const FlightContext = createContext<FlightContextType>({} as FlightContextType);

export function FlightProvider({ children }: { children: ReactNode }) {
  const [airlinesWithFlights, setAirlinesWithFlights] = useState<AirlineWithFlights[]>([]);
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
      const response = await api.get('/enterprise/flights');
      setAirlinesWithFlights(response.data);
    } catch (err) {
      setError('Failed to fetch flights');
    } finally {
      setLoading(false);
    }
  };

  // Flatten flights from all airlines for easier consumption by components
  const getAllFlights = (): Flight[] => {
    return airlinesWithFlights.flatMap(airline => airline.flights || []);
  };

  const addFlight = async (flight: FlightFormData) => {
    try {
      const response = await api.post('/flights', flight);
      await fetchFlights(); // Refresh the full list after adding
    } catch (err) {
      setError('Failed to add flight');
      throw err;
    }
  };

  const updateFlight = async (id: number, data: Partial<Flight>) => {
    try {
      const response = await api.put(`/flights/${id}`, data);
      await fetchFlights(); // Refresh the full list after updating
    } catch (err) {
      setError('Failed to update flight');
      throw err;
    }
  };

  const deleteFlight = async (id: number) => {
    try {
      await api.delete(`/flights/${id}`);
      await fetchFlights(); // Refresh the full list after deleting
    } catch (err) {
      setError('Failed to delete flight');
      throw err;
    }
  };

  return (
    <FlightContext.Provider
      value={{
        flights: getAllFlights(),
        addFlight,
        updateFlight,
        deleteFlight,
        loading,
        error,
        fetchFlights
      }}
    >
      {children}
    </FlightContext.Provider>
  );
}

export const useFlights = () => useContext(FlightContext);
