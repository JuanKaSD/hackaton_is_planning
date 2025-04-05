"use client";
import { useState, useMemo } from 'react';
import { useFlights } from '../../../contexts/FlightContext';
import { useAirlines } from '../../../contexts/AirlineContext';
import { useAirports } from '../../../contexts/AirportContext';
import { Dropdown } from '../../Dropdown';
import styles from '../../../styles/Tabs.module.css';
import { Plane, Plus, X } from 'lucide-react'; // Añadir importaciones de iconos

export function FlightTab() {
  const { flights, addFlight, updateFlight, deleteFlight, loading, fetchFlights } = useFlights();
  const { airlines } = useAirlines();
  const { airports, loading: airportsLoading } = useAirports();
  
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);
  const [isAddModalOpen, setIsAddModalOpen] = useState(false); // Nuevo estado para el modal de Add Flight
  const [selectedFlight, setSelectedFlight] = useState(null);
  const [editFormData, setEditFormData] = useState({
    origin: '',
    destination: '',
    duration: '',
    flight_date: '',
    status: '',
    passenger_capacity: '',
    airline_id: ''
  });

  const [formData, setFormData] = useState({
    origin: '',
    destination: '',
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
      origin: '',
      destination: '',
      duration: '',
      flight_date: '',
      status: 'available',
      passenger_capacity: '',
      airline_id: airlines[0]?.id || '' // Reset with the first airline if it exists
    });
    setIsAddModalOpen(false); // Cerrar el modal después de enviar
    // Refresh flights list after adding a new flight
    await fetchFlights();
  };

  const handleEdit = (flight) => {
    setSelectedFlight(flight);
    setEditFormData({
      origin: flight.origin,
      destination: flight.destination,
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

  const groupedFlights = useMemo(() => {
    const grouped = {};
    flights.forEach(flight => {
      const airline = airlines.find(a => a.id === flight.airline_id);
      if (!grouped[airline?.id]) {
        grouped[airline?.id] = {
          airlineName: airline?.name || 'Unknown Airline',
          flights: []
        };
      }
      grouped[airline?.id].flights.push(flight);
    });
    return grouped;
  }, [flights, airlines]);

  if (airlines.length === 0) {
    return (
      <div className={styles.emptyState}>
        <h2 className={styles.tabTitle}>Flights Management</h2>
        <p>You need to create at least one airline before managing flights.</p>
      </div>
    );
  }

  return (
    <div className={styles.tabContainer}>
      {/* Añadir encabezado con botón "Add Flight" */}
      <div className={`${styles.header} ${styles.rowLayout}`}>
        <h2 className={`${styles.tabTitle} ${styles.centerVertical}`}>
          <Plane className={styles.titleIcon} />
          Manage Flights
        </h2>
        <button 
          onClick={() => setIsAddModalOpen(true)} 
          className={styles.addButton}
        >
          <Plus size={20} />
          Add Flight
        </button>
      </div>

      {loading || airportsLoading ? (
        <p>Loading...</p>
      ) : (
        <>
          {/* Lista de vuelos existente */}
          <div className={styles.list}>
            {Object.entries(groupedFlights).map(([airlineId, data]) => (
              <div key={airlineId} className={styles.airlineGroup}>
                <h3 className={styles.airlineTitle}>{data.airlineName}</h3>
                <div className={styles.flightsList}>
                  {data.flights.map((flight) => (
                    <div key={flight.id} className={styles.item}>
                      <div>
                        <strong>{flight.origin} → {flight.destination}</strong>
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
              </div>
            ))}
          </div>

          {/* Modal para añadir vuelo */}
          {isAddModalOpen && (
            <div className={styles.modal}>
              <div className={styles.modalContent}>
                <div className={`${styles.modalHeader} ${styles.rowLayout}`}>
                  <h3 className={styles.centerVertical}>
                    <Plus className={styles.modalIcon} />
                    Add Flight
                  </h3>
                  <button onClick={() => setIsAddModalOpen(false)} className={styles.closeButton}>
                    <X size={20} />
                  </button>
                </div>
                <form onSubmit={handleSubmit} className={styles.form}>
                  {renderAirlineSelector()}
                  <Dropdown
                    label="Origin Airport"
                    value={formData.origin}
                    onChange={(value) => setFormData({ ...formData, origin: value })}
                    options={airports.map((airport) => ({
                      value: airport.id,
                      label: airport.id
                    }))}
                    placeholder="Select Origin Airport"
                    required={true}
                  />
                  <Dropdown
                    label="Destination Airport"
                    value={formData.destination}
                    onChange={(value) => setFormData({ ...formData, destination: value })}
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
                  <div className={styles.modalActions}>
                    <button type="submit">Add Flight</button>
                    <button type="button" onClick={() => setIsAddModalOpen(false)}>
                      Cancel
                    </button>
                  </div>
                </form>
              </div>
            </div>
          )}

          {/* Modal de edición existente */}
          {isEditModalOpen && (
            <div className={styles.modal}>
              <div className={styles.modalContent}>
                <h3>Edit Flight</h3>
                <form onSubmit={handleUpdate}>
                  {renderAirlineSelector()}
                  <Dropdown
                    label="Origin Airport"
                    value={editFormData.origin}
                    onChange={(value) => setEditFormData({ ...editFormData, origin: value })}
                    options={airports.map((airport) => ({
                      value: airport.id,
                      label: airport.id
                    }))}
                    placeholder="Select Origin Airport"
                    required={true}
                  />
                  <Dropdown
                    label="Destination Airport"
                    value={editFormData.destination}
                    onChange={(value) => setEditFormData({ ...editFormData, destination: value })}
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
