import debug from 'debug';

if (process.env.NODE_ENV === 'development') {
  debug.enable('auth:* api:* app:*');
}

export const createLogger = (namespace: string) => {
  const logger = debug(namespace);
  
  return {
    debug: (...args: any[]) => logger(...args),
    error: (...args: any[]) => logger('[ERROR]', ...args),
    info: (...args: any[]) => logger('[INFO]', ...args),
    warn: (...args: any[]) => logger('[WARN]', ...args),
  };
};

export default createLogger;
