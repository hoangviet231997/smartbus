import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { ReceiptsComponent } from './receipts/receipts.component';
import { TicketDestroysComponent } from './ticket-destroys/ticket-destroys.component';
import { ShiftDestroysComponent } from "./shift-destroys/shift-destroys.component";
import { InvoicesComponent } from './invoices/invoices.component';
import { DailiesComponent } from './dailies/dailies.component';
import { StaffsComponent } from './staffs/staffs.component';
import { TicketsComponent } from './tickets/tickets.component';
import { VehiclesComponent } from './vehicles/vehicles.component';
import { VehiclesAllComponent } from './vehicles-all/vehicles-all.component';
import { VehiclesPeriodComponent } from './vehicles-period/vehicles-period.component';
import { HistoryShiftsComponent } from './history-shifts/history-shifts.component';
import { CardsComponent } from './cards/cards.component';
import { PrintTicketsComponent } from './print-tickets/print-tickets.component';
import { TransactionDetailsComponent } from './transaction-details/transaction-details.component';
import { TransactionOnlinesComponent } from './transaction-onlines/transaction-onlines.component';
import { CardMonthsComponent } from './card-months/card-months.component';
import { TimeTripsComponent } from './time-trips/time-trips.component';
import { OutputComponent } from './output/output.component';
import { CardMonthGroupBusstationsComponent } from './card-month-group-busstations/card-month-group-busstations.component';
import { CardExemptionComponent } from './card-exemption/card-exemption.component';
import { ShiftSupervisorComponent} from './shift-supervisor/shift-supervisor.component';
import { VehiclesRoutesPeriodComponent } from "./vehicles-routes-period/vehicles-routes-period.component";

const routes: Routes = [
  {
    path: 'receipts',
    component: ReceiptsComponent
  },
  {
    path: 'ticket-destroy',
    component: TicketDestroysComponent
  },
  {
    path: 'shift_destroys',
    component: ShiftDestroysComponent
  },
  {
    path: 'time-trips',
    component: TimeTripsComponent
  },
  {
    path: 'invoices',
    component: InvoicesComponent
  },
  {
    path: 'dailies',
    component: DailiesComponent
  },
  {
    path: 'staffs',
    component: StaffsComponent
  },
  {
    path: 'tickets',
    component: TicketsComponent
  },
  {
    path: 'print-tickets',
    component: PrintTicketsComponent
  },
  {
    path: 'cards',
    component: CardsComponent
  },
  {
    path: 'card-months',
    component: CardMonthsComponent
  },
  {
    path: 'vehicles',
    component: VehiclesComponent
  },
  {
    path: 'vehicles-all',
    component: VehiclesAllComponent
  },
  {
    path: 'vehicles-period',
    component: VehiclesPeriodComponent
  },
  {
    path: 'history-shift',
    component: HistoryShiftsComponent
  },
  {
    path: 'transaction-detail',
    component: TransactionDetailsComponent
  },
  {
    path: 'transaction-online',
    component: TransactionOnlinesComponent
  },
  {
    path: 'output',
    component: OutputComponent
  },
  {
    path: 'card-month-group-station',
    component: CardMonthGroupBusstationsComponent
  },
  {
    path: 'card-exemption',
    component: CardExemptionComponent
  },
  {
    path: 'shift-supervisor',
    component: ShiftSupervisorComponent
  },
  {
    path: 'vehicles-routes-period',
    component: VehiclesRoutesPeriodComponent
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class ReportsRoutingModule { }
