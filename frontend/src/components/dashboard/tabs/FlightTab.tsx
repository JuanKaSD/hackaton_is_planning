"use client";
import { useState } from 'react';
import { useFlights } from '../../../contexts/FlightContext';
import { useAirlines } from '../../../contexts/AirlineContext';
import { useAirports } from '../../../contexts/AirportContext';
import { Dropdown } from '../../Dropdown';
import styles from '../../../styles/Tabs.module.css';

export function FlightTab() {
  const { flights, addFlight, updateFlight, deleteFlight, loading, fetchFlights } = useFlights();
  const { airlines } = useAirlines();
  const { airports, loading: airportsLoading } = useAirports();
  
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);
  const [selectedFlight, setSelectedFlight] = useState(null);
  const [editFormData, setEditFormData] = useState({
    origin_id: '',
    destination_id: '',
    duration: '',
    flight_date: '',
    status: '',
    passenger_capacity: '',
    airline_id: ''
  });

  const [formData, setFormData] = useState({
    origin_id: '',
    destination_id: '',
    duration: '',
    flight_date: '',
    status: 'available',
    passenger_capacity: '',
    airline_id: airlines[0]?.id || '' // Initialize with the first airline if it exists
  });

  const statusOptions = [
    { value: 'available', label: 'Available' },
    { value: 'unavailable', label: 'Unavailable' },
    { value: 'canceled', label: 'Canceled' }
  ];

  const handleSubmit = async (e) => {
    e.preventDefault();
    await addFlight({
      ...formData,
      duration: parseInt(formData.duration),
      passenger_capacity: parseInt(formData.passenger_capacity),
      status: 'available' // Ensure status is 'available' when submitting
    });
    setFormData({
      origin_id: '',
      destination_id: '',
      duration: '',
      flight_date: '',
      status: 'available',
      passenger_capacity: '',
      airline_id: airlines[0]?.id || '' // Reset with the first airline if it exists
    });
    // Refresh flights list after adding a new flight
    await fetchFlights();
  };

  const handleEdit = (flight) => {
    setSelectedFlight(flight);
    setEditFormData({
      origin_id: flight.origin_id,
      destination_id: flight.destination_id,
      duration: flight.duration.toString(),
      flight_date: flight.flight_date.split('.')[0], // Remove milliseconds if any
      status: flight.status,
      passenger_capacity: flight.passenger_capacity.toString(),
      airline_id: flight.airline_id.toString()
    });
    setIsEditModalOpen(true);
  };

  const handleUpdate = async (e) => {
    e.preventDefault();
    try {
      await updateFlight(selectedFlight.id, {
        ...editFormData,
        duration: parseInt(editFormData.duration),
        passenger_capacity: parseInt(editFormData.passenger_capacity),
      });
      setIsEditModalOpen(false);
      await fetchFlights();
    } catch (error) {
      console.error('Error updating flight:', error);
    }
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
      {loading || airportsLoading ? (
        <p>Loading...</p>
      ) : (
        <>
          <form onSubmit={handleSubmit} className={styles.form}>
            {renderAirlineSelector()}
            <Dropdown
              label="Origin Airport"
              value={formData.origin_id}
              onChange={(value) => setFormData({ ...formData, origin_id: value })}
              options={airports.map((airport) => ({
                value: airport.id,
                label: airport.id
              }))}
              placeholder="Select Origin Airport"
              required={true}
            />
            <Dropdown
              label="Destination Airport"
              value={formData.destination_id}
              onChange={(value) => setFormData({ ...formData, destination_id: value })}
              options={airports.map((airport) => ({
                value: airport.id,
                label: airport.id
              }))}
              placeholder="Select Destination Airport"
              required={true}
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
            <Dropdown
              label="Status"
              value={formData.status}
              onChange={(value) => setFormData({ ...formData, status: value })}
              options={statusOptions}
              placeholder="Select Status"
              required={true}
            />
            <button type="submit">Add Flight</button>
          </form>

          <div className={styles.list}>
            {flights.map((flight) => (
              <div key={flight.id} className={styles.item}>
                <div>
                  <strong>From: {flight.origin_id} → To: {flight.destination_id}</strong>
                  <p>Date: {new Date(flight.flight_date).toLocaleDateString()}</p>
                  <p>Duration: {flight.duration} minutes</p>
                  <p>Status: {flight.status}</p>
                  <p>Capacity: {flight.passenger_capacity} passengers</p>
                </div>
                <div className={styles.actions}>
                  <button onClick={() => handleEdit(flight)} className={styles.editButton}>
                    Edit
                  </button>
                  <button onClick={() => deleteFlight(flight.id)} className={styles.deleteButton}>
                    Delete
                  </button>
                </div>
              </div>
            ))}
          </div>

          {isEditModalOpen && (
            <div className={styles.modal}>
              <div className={styles.modalContent}>
                <h3>Edit Flight</h3>
                <form onSubmit={handleUpdate}>
                  {renderAirlineSelector()}
                  <Dropdown
                    label="Origin Airport"
                    value={editFormData.origin_id}
                    onChange={(value) => setEditFormData({ ...editFormData, origin_id: value })}
                    options={airports.map((airport) => ({
                      value: airport.id,
                      label: airport.id
                    }))}
                    placeholder="Select Origin Airport"
                    required={true}
                  />
                  <Dropdown
                    label="Destination Airport"
                    value={editFormData.destination_id}
                    onChange={(value) => setEditFormData({ ...editFormData, destination_id: value })}
                    options={airports.map((airport) => ({
                      value: airport.id,
                      label: airport.id
                    }))}
                    placeholder="Select Destination Airport"
                    required={true}
                  />
                  <input
                    type="number"
                    placeholder="Duration (minutes)"
                    value={editFormData.duration}
                    onChange={(e) => setEditFormData({ ...editFormData, duration: e.target.value })}
                    required
                  />
                  <input
                    type="datetime-local"
                    value={editFormData.flight_date}
                    onChange={(e) => setEditFormData({ ...editFormData, flight_date: e.target.value })}
                    required
                  />
                  <input
                    type="number"
                    placeholder="Passenger Capacity"
                    value={editFormData.passenger_capacity}
                    onChange={(e) => setEditFormData({ ...editFormData, passenger_capacity: e.target.value })}
                    required
                  />
                  <Dropdown
                    label="Status"
                    value={editFormData.status}
                    onChange={(value) => setEditFormData({ ...editFormData, status: value })}
                    options={statusOptions}
                    placeholder="Select Status"
                    required={true}
                  />
                  <div className={styles.modalActions}>
                    <button type="submit">Update Flight</button>
                    <button type="button" onClick={() => setIsEditModalOpen(false)}>
                      Cancel
                    </button>
                  </div>
                </form>
              </div>
            </div>
          )}
        </>
      )}
    </div>
  );
}
