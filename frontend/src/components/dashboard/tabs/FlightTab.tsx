"use client";
import { useState } from 'react';
import { useFlights } from '../../../contexts/FlightContext';
import { useAirlines } from '../../../contexts/AirlineContext';
import { Dropdown } from '../../Dropdown';
import styles from '../../../styles/Tabs.module.css';

export function FlightTab() {
  const { flights, addFlight, deleteFlight, loading, fetchFlights } = useFlights();
  const { airlines } = useAirlines();
  
  const [formData, setFormData] = useState({
    origin: '',
    destination: '',
    duration: '',
    flight_date: '',
    status: 'available',
    passenger_capacity: '',
    airline_id: airlines[0]?.id || '' // Initialize with the first airline if it exists
  });

  const handleSubmit = async (e) => {
    e.preventDefault();
    await addFlight({
      ...formData,
      duration: parseInt(formData.duration),
      passenger_capacity: parseInt(formData.passenger_capacity),
      status: 'available' // Ensure status is 'available' when submitting
    });
    setFormData({
      origin: '',
      destination: '',
      duration: '',
      flight_date: '',
      status: 'available',
      passenger_capacity: '',
      airline_id: airlines[0]?.id || '' // Reset with the first airline if it exists
    });
    // Refresh flights list after adding a new flight
    await fetchFlights();
  };

  const renderAirlineSelector = () => {
    if (airlines.length === 1) {
      return (
        <div className={styles.formField}>
          <label>Airline:</label>
          <span className={styles.staticText}>{airlines[0].name}</span>
        </div>
      );
    }
    
    return (
      <Dropdown
        label="Airline"
        value={formData.airline_id}
        onChange={(value) => setFormData({ ...formData, airline_id: value })}
        options={airlines.map((airline) => ({
          value: airline.id,
          label: airline.name,
        }))}
        placeholder="Select Airline"
        required={true}
      />
    );
  };

  if (airlines.length === 0) {
    return (
      <div className={styles.emptyState}>
        <h2 className={styles.tabTitle}>Flights Management</h2>
        <p>You need to create at least one airline before managing flights.</p>
      </div>
    );
  }

  return (
    <div>
      <h2 className={styles.tabTitle}>Manage Flights</h2>
      {loading ? (
        <p>Loading flights...</p>
      ) : (
        <>
          <form onSubmit={handleSubmit} className={styles.form}>
            {renderAirlineSelector()}
            <input
              type="text"
              placeholder="Origin City"
              value={formData.origin}
              onChange={(e) => setFormData({ ...formData, origin: e.target.value })}
              required
            />
            <input
              type="text"
              placeholder="Destination City"
              value={formData.destination}
              onChange={(e) => setFormData({ ...formData, destination: e.target.value })}
              required
            />
            <input
              type="number"
              placeholder="Duration (minutes)"
              value={formData.duration}
              onChange={(e) => setFormData({ ...formData, duration: e.target.value })}
              required
            />
            <input
              type="datetime-local"
              placeholder="Flight Date"
              value={formData.flight_date}
              onChange={(e) => setFormData({ ...formData, flight_date: e.target.value })}
              required
            />
            <input
              type="number"
              placeholder="Passenger Capacity"
              value={formData.passenger_capacity}
              onChange={(e) => setFormData({ ...formData, passenger_capacity: e.target.value })}
              required
            />
            <button type="submit">Add Flight</button>
          </form>

          <div className={styles.list}>
            {flights.map((flight) => (
              <div key={flight.id} className={styles.item}>
                <div>
                  <strong>{flight.origin} → {flight.destination}</strong>
                  <p>Date: {new Date(flight.flight_date).toLocaleDateString()}</p>
                  <p>Duration: {flight.duration} minutes</p>
                  <p>Status: {flight.status}</p>
                  <p>Capacity: {flight.passenger_capacity} passengers</p>
                </div>
                <button onClick={() => deleteFlight(flight.id)}>Delete</button>
              </div>
            ))}
          </div>
        </>
      )}
    </div>
  );
}
