import styles from '@/styles/Home.module.css';
import Link from 'next/link';

export default function HomePage() {
  return (
    <div className={styles.container}>
      <section className={styles.hero}>
        <h1>Find Your Next Adventure</h1>
        <p>Your one-stop platform for finding and booking the best flight deals worldwide</p>
        <div className={styles.actions}>
          <Link href="/login" className={styles.primaryButton}>
            Start Exploring
          </Link>
          <Link href="/signup" className={styles.secondaryButton}>
            Join Us
          </Link>
        </div>
      </section>
      <section className={styles.features}>
        <div className={styles.feature}>
          <h3>Best Deals</h3>
          <p>Find the most competitive prices for your flights</p>
        </div>
        <div className={styles.feature}>
          <h3>Easy Booking</h3>
          <p>Simple and secure flight reservation process</p>
        </div>
        <div className={styles.feature}>
          <h3>24/7 Support</h3>
          <p>Round-the-clock customer service for your needs</p>
        </div>
      </section>
    </div>
  );
}
