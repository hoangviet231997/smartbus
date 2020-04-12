/* tslint:disable */
import { DevModel } from './dev-model';

/**
 */
export class ManagerDevice {
    id?: number;
    device_model?: DevModel;
    identity?: string;
    is_running?: number;
    version?: number;
    lat?: number;
    lng?: number;
    created_at?: string;
    updated_at?: string;
}
