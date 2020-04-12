/* tslint:disable */
import { User } from './user';
import { Vehicle } from './vehicle';
import { Device } from './device';

/**
 */
export class MachineLoginView {
    user?: User;
    vehicle?: Vehicle;
    device?: Device;
    started?: string;
    shift_token?: string;
}
