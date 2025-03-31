"use client";
import { useState, useEffect } from 'react';
import { useAuth } from '@/contexts/AuthContext';
import { useRouter } from 'next/navigation';
import styles from './profile.module.css';

export default function ProfilePage() {
  const router = useRouter();
  const { user, updateUserField, updatePassword, deleteAccount } = useAuth();
  const [editingField, setEditingField] = useState<string | null>(null);
  const [fieldValues, setFieldValues] = useState({
    name: '',
    email: '',
    phone: ''
  });

  const [passwordData, setPasswordData] = useState({
    currentPassword: '',
    newPassword: '',
    confirmPassword: ''
  });

  const [error, setError] = useState('');
  const [successMessage, setSuccessMessage] = useState('');

  useEffect(() => {
    if (user) {
      setFieldValues({
        name: user.name || '',
        email: user.email || '',
        phone: user.phone || ''
      });
    }
  }, [user]);

  const handleFieldEdit = (field: string) => {
    setEditingField(field);
    setError('');
    setSuccessMessage('');
  };

  const handleFieldUpdate = async (field: string) => {
    try {
      await updateUserField(field, fieldValues[field]);
      setSuccessMessage(`${field.charAt(0).toUpperCase() + field.slice(1)} updated successfully!`);
      setEditingField(null);
    } catch (err) {
      setError(`Error updating ${field}`);
    }
  };

  const handlePasswordUpdate = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setSuccessMessage('');
    
    if (passwordData.newPassword !== passwordData.confirmPassword) {
      setError("Passwords don't match");
      return;
    }

    try {
      await updatePassword(passwordData.currentPassword, passwordData.newPassword);
      setSuccessMessage('Password updated successfully!');
      setPasswordData({
        currentPassword: '',
        newPassword: '',
        confirmPassword: ''
      });
    } catch (err) {
      setError('Error updating password');
    }
  };

  const handleDeleteAccount = async () => {
    const confirmed = window.confirm(
      "Are you sure you want to delete your account? This action cannot be undone."
    );
    
    if (confirmed) {
      try {
        await deleteAccount();
        router.push('/');
      } catch (err) {
        setError('Error deleting account');
      }
    }
  };

  return (
    <div className={styles.container}>
      <h1 className={styles.title}>Profile Settings</h1>
      
      {error && <div className={styles.errorMessage}>{error}</div>}
      {successMessage && <div className={styles.successMessage}>{successMessage}</div>}

      <div className={styles.section}>
        <h2 className={styles.sectionTitle}>Personal Information</h2>
        
        {Object.entries(fieldValues).map(([field, value]) => (
          <div key={field} className={styles.fieldGroup}>
            <label htmlFor={field}>{field.charAt(0).toUpperCase() + field.slice(1)}</label>
            <div className={styles.fieldContainer}>
              {editingField === field ? (
                <>
                  <input
                    type={field === 'email' ? 'email' : 'text'}
                    id={field}
                    value={fieldValues[field]}
                    onChange={(e) => setFieldValues(prev => ({
                      ...prev,
                      [field]: e.target.value
                    }))}
                  />
                  <div className={styles.fieldActions}>
                    <button
                      onClick={() => handleFieldUpdate(field)}
                      className={styles.saveButton}
                    >
                      Save
                    </button>
                    <button
                      onClick={() => setEditingField(null)}
                      className={styles.cancelButton}
                    >
                      Cancel
                    </button>
                  </div>
                </>
              ) : (
                <>
                  <span>{value}</span>
                  <button
                    onClick={() => handleFieldEdit(field)}
                    className={styles.editButton}
                  >
                    Edit
                  </button>
                </>
              )}
            </div>
          </div>
        ))}
      </div>

      <div className={styles.section}>
        <h2 className={styles.sectionTitle}>Change Password</h2>
        <form onSubmit={handlePasswordUpdate} className={styles.form}>
          <div className={styles.formGroup}>
            <label htmlFor="currentPassword">Current Password</label>
            <input
              type="password"
              id="currentPassword"
              value={passwordData.currentPassword}
              onChange={(e) => setPasswordData({...passwordData, currentPassword: e.target.value})}
              required
            />
          </div>

          <div className={styles.formGroup}>
            <label htmlFor="newPassword">New Password</label>
            <input
              type="password"
              id="newPassword"
              value={passwordData.newPassword}
              onChange={(e) => setPasswordData({...passwordData, newPassword: e.target.value})}
              required
            />
          </div>

          <div className={styles.formGroup}>
            <label htmlFor="confirmPassword">Confirm New Password</label>
            <input
              type="password"
              id="confirmPassword"
              value={passwordData.confirmPassword}
              onChange={(e) => setPasswordData({...passwordData, confirmPassword: e.target.value})}
              required
            />
          </div>

          <button type="submit" className={styles.submitButton}>
            Update Password
          </button>
        </form>
      </div>

      <div className={styles.dangerZone}>
        <h2 className={styles.sectionTitle}>Danger Zone</h2>
        <p className={styles.dangerText}>
          Once you delete your account, there is no going back. Please be certain.
        </p>
        <button 
          onClick={handleDeleteAccount}
          className={styles.deleteButton}
        >
          Delete Account
        </button>
      </div>
    </div>
  );
}
