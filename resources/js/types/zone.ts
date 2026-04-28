export type LatLng = { lat: number; lng: number };

export interface Zone {
    id: number;
    name: string;
    center_lat: number;
    center_lng: number;
    default_zoom: number;
    polygon?: LatLng[] | null;
    thumbnail_url?: string;
    user_ids: number[];
}
