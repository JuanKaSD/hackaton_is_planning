"use client";
import { useState } from 'react';
import { useAirlines } from '@/contexts/AirlineContext';
import { Dropdown } from '@/components/Dropdown';
import { EditAirlineModal } from '@/components/EditAirlineModal';
import styles from '@/styles/Tabs.module.css';
import { Plane, Edit2, Trash2, Plus, X } from 'lucide-react';

interface Airline {
  id: string;
  name: string;
}

export function AirlineTab() {
  const { airlines, addAirline, updateAirline, deleteAirline, loading, fetchAirlines } = useAirlines();
  const [name, setName] = useState('');
  const [editingAirline, setEditingAirline] = useState<Airline | null>(null);
  const [isAddModalOpen, setIsAddModalOpen] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    await addAirline({ name });
    setName('');
    setIsAddModalOpen(false);
    await fetchAirlines();
  };

  const handleUpdate = async (id: string, data: { name: string }) => {
    try {
      await updateAirline(id, data);
      setEditingAirline(null);
      await fetchAirlines();
    } catch (err) {
      console.error('Error updating airline:', err);
    }
  };

  const handleDelete = async (id: string) => {
    try {
      await deleteAirline(id);
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
    setIsAddModalOpen(false);
  };

  return (
    <div>
      <div className={`${styles.header} ${styles.rowLayout}`}>
        <h2 className={`${styles.tabTitle} ${styles.centerVertical}`}>
          <Plane className={styles.titleIcon} />
          Manage Airlines
        </h2>
        <button
          onClick={() => setIsAddModalOpen(true)}
          className={styles.addButton}
        >
          <Plus size={20} />
          Add Airline
        </button>
      </div>
      {loading ? (
        <p>Loading airlines...</p>
      ) : (
        <>
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

          {isAddModalOpen && (
            <div className={`${styles.modal} ${styles.rowLayout}`}>
              <div className={styles.modalContent}>
                <div className={`${styles.modalHeader} ${styles.rowLayout}`}>
                  <h3 className={styles.centerVertical}>
                    <Plus className={styles.modalIcon} />
                    Add Airline
                  </h3>
                  <button onClick={closeModal} className={styles.closeButton}>
                    <X size={20} />
                  </button>
                </div>
                <form onSubmit={handleSubmit}>
                  <input
                    type="text"
                    placeholder="Airline Name"
                    value={name}
                    onChange={(e) => setName(e.target.value)}
                    required
                  />
                  <div className={styles.modalActions}>
                    <button type="submit">Add Airline</button>
                    <button type="button" onClick={closeModal}>
                      Cancel
                    </button>
                  </div>
                </form>
              </div>
            </div>
          )}

          {editingAirline && (
            <div className={`${styles.modal} ${styles.rowLayout}`}>
              <div className={styles.modalContent}>
                <div className={`${styles.modalHeader} ${styles.rowLayout}`}>
                  <h3 className={styles.centerVertical}>
                    <Edit2 className={styles.modalIcon} />
                    Edit Airline
                  </h3>
                  <button onClick={closeModal} className={styles.closeButton}>
                    <X size={20} />
                  </button>
                </div>
                <form
                  onSubmit={(e) => {
                    e.preventDefault();
                    handleUpdate(editingAirline.id, { name: editingAirline.name });
                  }}
                >
                  <input
                    type="text"
                    value={editingAirline.name}
                    onChange={(e) =>
                      setEditingAirline({
                        ...editingAirline,
                        name: e.target.value,
                      })
                    }
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
