"use client";
import { useState } from 'react';
import { AirlineTab } from './tabs/AirlineTab';
import { FlightTab } from './tabs/FlightTab';
import styles from '@/styles/Dashboard.module.css';
import { PlaneIcon, CalendarIcon } from 'lucide-react';

type TabType = 'airlines' | 'flights';

export function DashboardTabs() {
  const [activeTab, setActiveTab] = useState<TabType>('airlines');

  return (
    <div className={styles.tabsContainer}>
      <div className={styles.tabs}>
        <button 
          className={`${styles.tab} ${activeTab === 'airlines' ? styles.active : ''}`}
          onClick={() => setActiveTab('airlines')}
        >
          <PlaneIcon className={styles.tabIcon} size={18} />
          Airlines
        </button>
        <button 
          className={`${styles.tab} ${activeTab === 'flights' ? styles.active : ''}`}
          onClick={() => setActiveTab('flights')}
        >
          <CalendarIcon className={styles.tabIcon} size={18} />
          Flights
        </button>
      </div>
      
      <div className={styles.tabContent}>
        {activeTab === 'airlines' && <AirlineTab />}
        {activeTab === 'flights' && <FlightTab />}
      </div>
    </div>
  );
}
