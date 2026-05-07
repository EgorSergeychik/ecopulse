export const METRIC_KEYS = [
    'battery_pct',
    'co2',
    'noise_level',
    'pm25',
    'temperature',
    'humidity',
    'aqi',
    'radiation',
] as const;

export type MetricKey = (typeof METRIC_KEYS)[number];
