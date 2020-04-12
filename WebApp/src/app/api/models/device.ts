/* tslint:disable */
import { DevModel } from './dev-model';
import { Company } from './company';

/**
 */
export class Device {
    id?: number;
    device_model?: DevModel;
    company?: Company;
    identity?: string;
    is_running?: number;
    version?: number;
    lat?: number;
    lng?: number;
    created_at?: string;
    updated_at?: string;
}
