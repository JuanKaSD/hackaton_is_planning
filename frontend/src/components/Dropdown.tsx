import React, { useState, useMemo, useRef, useEffect } from 'react';
import styles from '@/styles/Dropdown.module.css';

interface DropdownProps {
  value: string;
  onChange: (value: string) => void;
  options: Array<{
    value: string;
    label: string;
  }>;
  placeholder?: string;
  className?: string;
  required?: boolean;
  disabled?: boolean;
  label?: string;
  error?: string;
  filterPlaceholder?: string;
}

export const Dropdown: React.FC<DropdownProps> = ({
  value,
  onChange,
  options,
  placeholder = 'Select an option',
  className = '',
  required = false,
  disabled = false,
  label,
  error,
  filterPlaceholder = 'Search...'
}) => {
  const [isOpen, setIsOpen] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');
  const dropdownRef = useRef<HTMLDivElement>(null);

  const filteredOptions = useMemo(() => {
    return options.filter(option => 
      option.label.toLowerCase().includes(searchTerm.toLowerCase())
    );
  }, [options, searchTerm]);

  const selectedOption = options.find(opt => opt.value === value);

  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
        setIsOpen(false);
      }
    };

    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  return (
    <div className={styles.container}>
      {label && <label className={styles.label}>{label}</label>}
      <div className={styles.dropdownContainer} ref={dropdownRef}>
        <div 
          className={`${styles.selectButton} ${isOpen ? styles.open : ''} ${error ? styles.error : ''} ${className}`}
          onClick={() => !disabled && setIsOpen(!isOpen)}
        >
          <span className={value ? styles.selectedValue : styles.placeholder}>
            {selectedOption ? selectedOption.label : placeholder}
          </span>
          <span className={styles.arrow}>▼</span>
        </div>
        
        {isOpen && !disabled && (
          <div className={styles.dropdown}>
            <div className={styles.searchContainer}>
              <input
                type="text"
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                placeholder={filterPlaceholder}
                className={styles.searchInput}
                onClick={(e) => e.stopPropagation()}
              />
            </div>
            <div className={styles.optionsList}>
              {filteredOptions.map((option) => (
                <div
                  key={option.value}
                  className={`${styles.option} ${value === option.value ? styles.selected : ''}`}
                  onClick={() => {
                    onChange(option.value);
                    setIsOpen(false);
                    setSearchTerm('');
                  }}
                >
                  {option.label}
                </div>
              ))}
            </div>
          </div>
        )}
      </div>
      {error && <span className={styles.errorMessage}>{error}</span>}
    </div>
  );
};
