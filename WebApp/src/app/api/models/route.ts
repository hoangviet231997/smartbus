/* tslint:disable */
import { BusStation } from './bus-station';

/**
 */
export class Route {
    module_data?: string;
    id?: number;
    start_time?: string;
    end_time?: string;
    number?: string;
    name?: string;
    comany_id?: number;
    ticket_data?: string;
    distance_scan?: number;
    timeout_sound?: number;
    bus_stations?: BusStation[];
    created_at?: string;
    updated_at?: string;
}
