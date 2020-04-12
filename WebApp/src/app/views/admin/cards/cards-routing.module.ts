import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { BlankCardsComponent } from './blank-cards/blank-cards.component';
import { PrepaidCardsComponent } from './prepaid-cards/prepaid-cards.component';
import { RfidCardsComponent } from './rfid-cards/rfid-cards.component';
import { MembershipTypeCardsComponent } from './membership-type-cards/membership-type-cards.component';
import { PrintBlankCardsComponent } from './print-blank-cards/print-blank-cards.component';

const routes: Routes = [
  {
    path: '',
    children: [
      {
        path: 'blank-cards',
        component: BlankCardsComponent
      },
      {
        path: 'prepaid-cards',
        component: PrepaidCardsComponent
      },
      {
        path: 'rfid-cards',
        component: RfidCardsComponent
      },
      {
        path: 'membership-type-cards',
        component: MembershipTypeCardsComponent
      },
      {
        path: 'print-blank-cards',
        component: PrintBlankCardsComponent
      }
    ]
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class CardsRoutingModule { }
