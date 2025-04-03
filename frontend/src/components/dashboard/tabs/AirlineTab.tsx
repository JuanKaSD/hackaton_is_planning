"use client";
import { useState } from 'react';
import { useAirlines } from '@/contexts/AirlineContext';
import { Dropdown } from '@/components/Dropdown';
import { EditAirlineModal } from '@/components/EditAirlineModal';
import styles from '@/styles/Tabs.module.css';

interface Airline {
  id: number;
  name: string;
}

export function AirlineTab() {
  const { airlines, addAirline, updateAirline, deleteAirline, loading } = useAirlines();
  const [name, setName] = useState('');
  const [editingAirline, setEditingAirline] = useState<Airline | null>(null);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    await addAirline({ name });
    setName('');
  };

  const handleUpdate = async (id: number, data: { name: string }) => {
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
            <EditAirlineModal
              airline={editingAirline}
              onClose={closeModal}
              onSave={handleUpdate}
            />
          )}
        </>
      )}
    </div>
  );
}
