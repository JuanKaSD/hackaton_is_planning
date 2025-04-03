import { useState } from 'react';
import styles from '../styles/Modal.module.css';

interface Airline {
  id: string; // Changed from number to string to match AirlineContext
  name: string;
}

interface EditAirlineModalProps {
  airline: Airline;
  onClose: () => void;
  onSave: (id: string, data: { name: string }) => Promise<void>; // Changed from number to string
}

export function EditAirlineModal({ airline, onClose, onSave }: EditAirlineModalProps) {
  const [name, setName] = useState(airline.name);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onSave(airline.id, { name });
  };

  return (
    <div className={styles.modalOverlay}>
      <div className={styles.modal}>
        <h2>Edit Airline</h2>
        <form onSubmit={handleSubmit}>
          <div className={styles.formGroup}>
            <label>Name:</label>
            <input
              type="text"
              value={name}
              onChange={(e) => setName(e.target.value)}
              required
            />
          </div>
          <div className={styles.buttonGroup}>
            <button type="submit" className={styles.saveButton}>Accept</button>
            <button type="button" onClick={onClose} className={styles.cancelButton}>Cancel</button>
          </div>
        </form>
      </div>
    </div>
  );
}
