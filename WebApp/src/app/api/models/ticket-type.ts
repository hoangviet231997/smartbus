/* tslint:disable */
import { TicketPrice } from './ticket-price';

/**
 */
export class TicketType {
    sign_form?: string;
    id?: number;
    duration?: number;
    name?: string;
    description?: string;
    order_code?: string;
    sign?: string;
    comany_id?: number;
    number_km?: number;
    sale_of?: number;
    language?: string;
    type?: number;
    ticket_prices?: TicketPrice[];
    created_at?: string;
    updated_at?: string;
}
