export interface Robot {
    id: number;
    name: string;
    mac_address: string | null;
    zone_id: number;
    zone_name?: string | null;
    status: string;
    status_label: string;
    battery_pct: number;
}
