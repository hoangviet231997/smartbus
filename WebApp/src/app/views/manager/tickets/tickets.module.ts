import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { TicketsRoutingModule } from './tickets-routing.module';
import { TicketsComponent } from './tickets/tickets.component';
import { TicketProvidersComponent } from './ticket-providers/ticket-providers.component';
import { DenominationGoodsComponent } from './denomination-goods/denomination-goods.component';
import { DenominationServiceComponent } from './denomination-service/denomination-service.component';

import { ModalModule, PaginationModule, BsDatepickerModule, TimepickerModule } from 'ngx-bootstrap';
import { FormsModule } from '@angular/forms';
import { LaddaModule } from 'angular2-ladda';
import { SelectModule } from 'ng2-select';
import { SharedModule } from '../../../shared/shared.module';
// import { ManagerModule } from '../manager.module';
import { ColorPickerModule } from 'ngx-color-picker';

@NgModule({
  imports: [
    CommonModule,
    TicketsRoutingModule,
    ModalModule,
    TimepickerModule.forRoot(),
    PaginationModule.forRoot(),
    FormsModule,
    LaddaModule.forRoot({
      style: 'slide-left',
    }),
    SelectModule,
    BsDatepickerModule.forRoot(),
    SharedModule,
    ColorPickerModule
    // ManagerModule
  ],
  declarations: [
    TicketsComponent,
    TicketProvidersComponent,
    DenominationGoodsComponent,
    DenominationServiceComponent
  ]
})
export class TicketsModule { }
