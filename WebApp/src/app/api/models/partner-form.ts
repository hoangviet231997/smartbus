/* tslint:disable */
import { Company } from './company';

/**
 */
export class PartnerForm {
    id?: number;
    partner_code?: string;
    is_check?: number;
    company_name?: string;
    company_fullname?: string;
    address?: string;
    phone?: string;
    url?: string;
    email?: string;
    group_company?: Company[];
}
