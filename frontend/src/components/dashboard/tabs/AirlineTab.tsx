"use client";
import { useState } from 'react';
import { useAirlines } from '@/contexts/AirlineContext';
import { Dropdown } from '@/components/Dropdown';
import { EditAirlineModal } from '@/components/EditAirlineModal';
import styles from '@/styles/Tabs.module.css';

interface Airline {
  id: string; // Changed from number to string to match AirlineContext
  name: string;
}

export function AirlineTab() {
  const { airlines, addAirline, updateAirline, deleteAirline, loading, fetchAirlines } = useAirlines();
  const [name, setName] = useState('');
  const [editingAirline, setEditingAirline] = useState<Airline | null>(null);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    await addAirline({ name });
    setName('');
  };

  const handleUpdate = async (id: string, data: { name: string }) => { // Changed from number to string
    try {
      await updateAirline(id, data);
      setEditingAirline(null);
    } catch (err) {
      console.error('Error updating airline:', err);
    }
  };

  const handleEditClick = (airline: Airline) => {
    setEditingAirline(airline);
  };

  const closeModal = () => {
    setEditingAirline(null);
  };

  return (
    <div>
      <h2 className={styles.tabTitle}>Manage Airlines</h2>
      {loading ? (
        <p>Loading airlines...</p>
      ) : (
        <>
          <form onSubmit={handleSubmit} className={styles.form}>
            <input
              type="text"
              placeholder="Airline Name"
              value={name}
              onChange={(e) => setName(e.target.value)}
              required
            />
            <button type="submit">Add Airline</button>
          </form>

          <div className={styles.list}>
            {airlines.map((airline) => (
              <div key={airline.id} className={styles.item}>
                <div>
                  <strong>{airline.name}</strong>
                </div>
                <div className={styles.buttonGroup}>
                  <button 
                    onClick={() => handleEditClick(airline)}
                    className={styles.editButton}
                  >
                    Edit
                  </button>
                  <button 
                    onClick={() => deleteAirline(airline.id)}
                    className={styles.deleteButton}
                  >
                    Delete
                  </button>
                </div>
              </div>
            ))}
          </div>

          {editingAirline && (
            <div className={styles.modal}>
              <div className={styles.modalContent}>
                <h3>Edit Airline</h3>
                <form onSubmit={(e) => {
                  e.preventDefault();
                  handleUpdate(editingAirline.id, { name: editingAirline.name });
                }}>
                  <input
                    type="text"
                    value={editingAirline.name}
                    onChange={(e) => setEditingAirline({
                      ...editingAirline,
                      name: e.target.value
                    })}
                    placeholder="Airline Name"
                    required
                  />
                  <div className={styles.modalActions}>
                    <button type="submit">Update Airline</button>
                    <button type="button" onClick={closeModal}>
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
