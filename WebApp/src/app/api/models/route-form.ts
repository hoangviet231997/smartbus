/* tslint:disable */
import { ModuleApp } from './module-app';
import { TicketPrice } from './ticket-price';
import { BusStation } from './bus-station';

/**
 */
export class RouteForm {
    id?: number;
    start_time?: string;
    end_time?: string;
    number?: string;
    name?: string;
    modules?: ModuleApp[];
    tickets?: TicketPrice[];
    bus_stations?: BusStation[];
    distance_scan?: number;
    timeout_sound?: number;
}
