/* tslint:disable */
import { Role } from './role';
import { Company } from './company';
import { Permission } from './permission';
import { RfidCard } from './rfid-card';

/**
 */
export class User {
    username?: string;
    id?: number;
    role?: Role;
    company_id?: number;
    company?: Company;
    permissions?: Permission[];
    pin_code?: number;
    rfidcard_id?: number;
    rfidcard?: RfidCard;
    role_id?: number;
    email?: string;
    fullname?: string;
    birthday?: string;
    address?: string;
    sidn?: string;
    gender?: number;
    phone?: string;
    created_at?: string;
    updated_at?: string;
}
