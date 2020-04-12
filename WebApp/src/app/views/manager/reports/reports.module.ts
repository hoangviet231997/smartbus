import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { ReportsRoutingModule } from './reports-routing.module';
import { ReceiptsComponent, FilterPipe} from './receipts/receipts.component';
import { FormsModule } from '@angular/forms';
import { ModalModule, BsDatepickerModule } from 'ngx-bootstrap';
import { LaddaModule } from 'angular2-ladda';
import { AuthenticationModule } from '../../../authentication/authentication.module';
import { SharedModule } from '../../../shared/shared.module';
import { TabsModule } from 'ngx-bootstrap';
import { SelectModule } from 'ng2-select';
import { StaffsComponent } from './staffs/staffs.component';
import { DailiesComponent } from './dailies/dailies.component';
import { VehiclesComponent,FilterVehiclePipe } from './vehicles/vehicles.component';
import { TicketsComponent } from './tickets/tickets.component';
import { InvoicesComponent } from './invoices/invoices.component';
import { NgxSpinnerModule } from 'ngx-spinner';
import { HistoryShiftsComponent } from './history-shifts/history-shifts.component';
import { CardsComponent } from './cards/cards.component';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { PrintTicketsComponent } from './print-tickets/print-tickets.component';
import { TransactionDetailsComponent } from './transaction-details/transaction-details.component';
import { VehiclesAllComponent } from './vehicles-all/vehicles-all.component';
import { VehiclesPeriodComponent } from './vehicles-period/vehicles-period.component';
import { TicketDestroysComponent } from './ticket-destroys/ticket-destroys.component';
import { ShiftDestroysComponent } from "./shift-destroys/shift-destroys.component";
import { TransactionOnlinesComponent } from './transaction-onlines/transaction-onlines.component';
import { CardMonthsComponent } from './card-months/card-months.component';
import { TimeTripsComponent } from './time-trips/time-trips.component';
import { OutputComponent } from './output/output.component';
import { CardMonthGroupBusstationsComponent } from './card-month-group-busstations/card-month-group-busstations.component';
import { CardExemptionComponent } from './card-exemption/card-exemption.component';
import { ShiftSupervisorComponent, FilterUserSupervisor} from './shift-supervisor/shift-supervisor.component';
import { VehiclesRoutesPeriodComponent } from './vehicles-routes-period/vehicles-routes-period.component';

@NgModule({
  imports: [
    CommonModule,
    ReportsRoutingModule,
    FormsModule,
    BsDatepickerModule.forRoot(),
    ModalModule,
    LaddaModule.forRoot({
      style: 'slide-left',
    }),
    SelectModule,
    AuthenticationModule,
    SharedModule,
    NgxSpinnerModule,
    TabsModule.forRoot(),
    NgbModule.forRoot()
  ],

  declarations: [
    ReceiptsComponent, FilterPipe, StaffsComponent, VehiclesComponent, DailiesComponent, TicketsComponent,
    InvoicesComponent, HistoryShiftsComponent, CardsComponent, PrintTicketsComponent, TransactionDetailsComponent,
    FilterVehiclePipe, VehiclesAllComponent, VehiclesPeriodComponent, TicketDestroysComponent, TransactionOnlinesComponent,
    CardMonthsComponent, ShiftDestroysComponent, TimeTripsComponent, OutputComponent, CardMonthGroupBusstationsComponent,
    CardExemptionComponent, ShiftSupervisorComponent, FilterUserSupervisor, VehiclesRoutesPeriodComponent]

})
export class ReportsModule { }
