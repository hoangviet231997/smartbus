/* tslint:disable */
import { ShiftData } from './shift-data';

/**
 */
export class TotalBill {
    count_charge_month?: number;
    license_plates?: string;
    route_number?: number;
    driver_name?: string;
    subdriver_name?: string;
    count_charge_free?: number;
    count_charge?: number;
    count_pos?: number;
    route_name?: string;
    count_online?: number;
    total_charge?: number;
    total_pos?: number;
    total_deposit?: number;
    total_deposit_month?: number;
    total_online?: number;
    shifts?: ShiftData[];
}
