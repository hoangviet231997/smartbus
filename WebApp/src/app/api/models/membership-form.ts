/* tslint:disable */
import { BusStation } from './bus-station';

/**
 */
export class MembershipForm {
    duration?: number;
    id?: number;
    membershiptype_id?: number;
    rfidcard_id?: number;
    fullname?: string;
    cmnd?: string;
    gender?: string;
    avatar?: string;
    address?: string;
    phone?: string;
    balance?: number;
    sidn?: string;
    email?: string;
    birthday?: string;
    company_id?: number;
    charge_limit?: number;
    charge_limit_prepaid?: number;
    actived?: number;
    expiration_date?: string;
    start_expiration_date?: string;
    ticket_price_id?: number;
    membership_type_name?: string;
    membership_type_deduction?: number;
    rfid?: string;
    barcode?: string;
    type_edit?: number;
    gr_bus_station_id?: number;
    station_data?: BusStation[];
}
