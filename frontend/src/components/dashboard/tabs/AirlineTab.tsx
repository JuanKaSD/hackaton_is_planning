"use client";
import { useState } from 'react';
import { useAirlines } from '@/contexts/AirlineContext';
import { Dropdown } from '@/components/Dropdown';
import { EditAirlineModal } from '@/components/EditAirlineModal';
import styles from '@/styles/Tabs.module.css';
import { Plane, Edit2, Trash2, Plus, X } from 'lucide-react';

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
    // Refresh airlines after adding
    await fetchAirlines();
  };

  const handleUpdate = async (id: string, data: { name: string }) => { // Changed from number to string
    try {
      await updateAirline(id, data);
      setEditingAirline(null);
      // Refresh airlines after updating
      await fetchAirlines();
    } catch (err) {
      console.error('Error updating airline:', err);
    }
  };

  const handleDelete = async (id: string) => {
    try {
      await deleteAirline(id);
      // Refresh airlines after deleting
      await fetchAirlines();
    } catch (err) {
      console.error('Error deleting airline:', err);
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
      <h2 className={styles.tabTitle}>
        <Plane className={styles.titleIcon} />
        Manage Airlines
      </h2>
      {loading ? (
        <p>Loading airlines...</p>
      ) : (
        <>
          <form onSubmit={handleSubmit} className={styles.form}>
            <div className={styles.inputWrapper}>
              <input
                type="text"
                placeholder="Airline Name"
                value={name}
                onChange={(e) => setName(e.target.value)}
                required
              />
              <button type="submit" className={styles.addButton}>
                <Plus size={20} />
                Add Airline
              </button>
            </div>
          </form>

          <div className={styles.list}>
            {airlines.map((airline) => (
              <div key={airline.id} className={styles.item}>
                <div className={styles.itemContent}>
                  <Plane className={styles.itemIcon} />
                  <strong>{airline.name}</strong>
                </div>
                <div className={styles.buttonGroup}>
                  <button 
                    onClick={() => handleEditClick(airline)}
                    className={styles.editButton}
                  >
                    <Edit2 size={16} />
                    Edit
                  </button>
                  <button 
                    onClick={() => handleDelete(airline.id)}
                    className={styles.deleteButton}
                  >
                    <Trash2 size={16} />
                    Delete
                  </button>
                </div>
              </div>
            ))}
          </div>

          {editingAirline && (
            <div className={styles.modal}>
              <div className={styles.modalContent}>
                <div className={styles.modalHeader}>
                  <h3>
                    <Edit2 className={styles.modalIcon} />
                    Edit Airline
                  </h3>
                  <button onClick={closeModal} className={styles.closeButton}>
                    <X size={20} />
                  </button>
                </div>
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
