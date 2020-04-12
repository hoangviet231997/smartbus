import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { CardsRoutingModule } from './cards-routing.module';
import { BlankCardsComponent } from './blank-cards/blank-cards.component';
import { PrepaidCardsComponent } from './prepaid-cards/prepaid-cards.component';
import { FormsModule } from '@angular/forms';
import { ModalModule, PaginationModule } from 'ngx-bootstrap';
import { LaddaModule } from 'angular2-ladda';
import { AuthenticationModule } from '../../../authentication/authentication.module';
import { SharedModule } from '../../../shared/shared.module';
import { RfidCardsComponent } from './rfid-cards/rfid-cards.component';
import { ApiModule } from '../../../api/api.module';
import { SelectModule } from 'ng2-select';
import { MembershipTypeCardsComponent } from './membership-type-cards/membership-type-cards.component';
import { PrintBlankCardsComponent } from './print-blank-cards/print-blank-cards.component';
import { QRCodeModule } from 'angularx-qrcode';

@NgModule({
  imports: [
    CommonModule,
    CardsRoutingModule,
    ModalModule,
    FormsModule,
    ApiModule,
    SelectModule,
    QRCodeModule,
    PaginationModule,
    LaddaModule.forRoot({
      style: 'slide-left',
    }),
    AuthenticationModule,
    SharedModule
  ],
  exports: [
    AuthenticationModule
  ],
  declarations: [BlankCardsComponent, PrepaidCardsComponent, RfidCardsComponent, MembershipTypeCardsComponent, PrintBlankCardsComponent]
})
export class CardsModule { }
