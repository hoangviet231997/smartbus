import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { NotifiesRoutingModule } from './notifies-routing.module';
import { AppNotifiesComponent } from './app-notifies/app-notifies.component';

import { SharedModule } from '../../../shared/shared.module';
import { ModalModule, PaginationModule, BsDatepickerModule, TimepickerModule } from 'ngx-bootstrap';
import { FormsModule } from '@angular/forms';
import { LaddaModule } from 'angular2-ladda';
import { SelectModule } from 'ng2-select';
import { WebNotifiesComponent } from './web-notifies/web-notifies.component';

@NgModule({
  imports: [
    CommonModule,
    NotifiesRoutingModule,
    SharedModule,
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
  ],
  declarations: [AppNotifiesComponent, WebNotifiesComponent]
})
export class NotifiesModule { }
