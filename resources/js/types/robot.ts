export interface Robot {
    id: number;
    name: string;
    mac_address: string | null;
    zone_id: number;
    zone_name?: string | null;
    status: string;
    status_label: string;
    battery_pct: number;
    lat?: number | null;
    lng?: number | null;
    latest_recorded_at?: string | null;
    latest_metrics?: Record<string, unknown>;
}
