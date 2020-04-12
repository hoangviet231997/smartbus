import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { MembershipsRoutingModule } from './memberships-routing.module';
import { MembershipsTmpComponent } from './memberships-tmp/memberships-tmp.component';
import { BlankCardsComponent } from './blank-cards/blank-cards.component';
import { MembershipCardsComponent } from './membership-cards/membership-cards.component';
import { MembershipTypeCardsComponent } from './membership-type-cards/membership-type-cards.component';
import { DenominationsComponent } from './denominations/denominations.component';

import { ModalModule, PaginationModule, BsDatepickerModule, TimepickerModule, TabsModule, BsDatepickerConfig } from 'ngx-bootstrap';
import { FormsModule } from '@angular/forms';
import { LaddaModule } from 'angular2-ladda';
import { SelectModule } from 'ng2-select';
import { AgmCoreModule } from '@agm/core';
import { QRCodeModule } from 'angularx-qrcode';
import { SharedModule } from '../../../shared/shared.module';
import { ChartModule } from 'angular-highcharts';
import { NgxSpinnerModule } from 'ngx-spinner';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

@NgModule({
  imports: [
    CommonModule,
    MembershipsRoutingModule,
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
    TabsModule.forRoot(),
    NgbModule.forRoot(),
    QRCodeModule,
    AgmCoreModule,
    ChartModule,
    NgxSpinnerModule
  ],
  declarations: [
    MembershipsTmpComponent,
    BlankCardsComponent,
    MembershipCardsComponent,
    MembershipTypeCardsComponent,
    DenominationsComponent
  ]
})
export class MembershipsModule { }
