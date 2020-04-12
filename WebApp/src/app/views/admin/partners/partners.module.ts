import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ModalModule, PaginationModule } from 'ngx-bootstrap';
import { TimepickerModule } from 'ngx-bootstrap';
import { BsDatepickerModule } from 'ngx-bootstrap';
import { AgmCoreModule } from '@agm/core';
import { FormsModule } from '@angular/forms';
import { LaddaModule } from 'angular2-ladda';
import { SelectModule } from 'ng2-select';
import { SharedModule } from '../../../shared/shared.module';

import { PartnersRoutingModule } from './partners-routing.module';
import { PartnersComponent } from './partners/partners.component';
import { PartnersAccountComponent } from './partners-account/partners-account.component';

@NgModule({
  imports: [
    CommonModule,
    PartnersRoutingModule,
    ModalModule,
    PaginationModule.forRoot(),
    TimepickerModule,
    BsDatepickerModule.forRoot(),
    FormsModule,
    LaddaModule.forRoot({
      style: 'slide-left',
    }),
    SelectModule,
    SharedModule,
    AgmCoreModule
  ],
  declarations: [PartnersComponent, PartnersAccountComponent]
})
export class PartnersModule { }
