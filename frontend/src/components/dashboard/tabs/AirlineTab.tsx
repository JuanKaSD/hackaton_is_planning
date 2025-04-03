"use client";
import { useState } from 'react';
import { useAirlines } from '@/contexts/AirlineContext';
import { Dropdown } from '@/components/Dropdown';
import styles from '@/styles/Tabs.module.css';

export function AirlineTab() {
  const { airlines, addAirline, updateAirline, deleteAirline, loading } = useAirlines();
  const [name, setName] = useState('');
  const [editingAirline, setEditingAirline] = useState<string | null>(null);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    await addAirline({ name });
    setName('');
  };

  const handleUpdate = async (id: string, newName: string) => {
    try {
      await updateAirline(id, { name: newName });
      setEditingAirline(null);
    } catch (err) {
      console.error('Error updating airline:', err);
    }
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
                  {editingAirline === airline.id ? (
                    <Dropdown
                      value={airline.name}
                      onChange={(value) => handleUpdate(airline.id, value)}
                      options={[
                        { value: airline.name, label: airline.name },
                        // Add more name options if needed
                      ]}
                      placeholder="Edit airline name"
                      className={styles.editDropdown}
                      onBlur={() => setEditingAirline(null)}
                    />
                  ) : (
                    <strong onClick={() => setEditingAirline(airline.id)}>
                      {airline.name}
                    </strong>
                  )}
                </div>
                <button onClick={() => deleteAirline(airline.id)}>Delete</button>
              </div>
            ))}
          </div>
        </>
      )}
    </div>
  );
}
