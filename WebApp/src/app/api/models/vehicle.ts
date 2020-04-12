/* tslint:disable */
import { Route } from './route';
import { RfidCard } from './rfid-card';

/**
 */
export class Vehicle {
    rfid?: string;
    id?: number;
    comany_id?: number;
    route_id?: number;
    device_id?: number;
    identity?: string;
    shift_id?: number;
    route?: Route;
    is_running?: number;
    license_plates?: string;
    rfidcard?: RfidCard;
    lat?: number;
    lng?: number;
    driver_name?: string;
    subdriver_name?: string;
    created_at?: string;
    updated_at?: string;
    device_imei?: string;
    bluetooth_mac_add?: string;
    bluetooth_pass?: string;
}
