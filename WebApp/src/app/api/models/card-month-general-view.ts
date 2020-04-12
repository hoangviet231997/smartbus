/* tslint:disable */
import { Transaction } from './transaction';

/**
 */
export class CardMonthGeneralView {
    rfid?: string;
    barcode?: string;
    fullname?: string;
    total_amount?: number;
    transactions?: Transaction[];
}
