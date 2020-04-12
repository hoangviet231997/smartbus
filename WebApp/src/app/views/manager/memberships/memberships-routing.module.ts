import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { BlankCardsComponent } from './blank-cards/blank-cards.component';
import { MembershipCardsComponent } from './membership-cards/membership-cards.component';
import { MembershipTypeCardsComponent } from './membership-type-cards/membership-type-cards.component';
import { MembershipsTmpComponent } from './memberships-tmp/memberships-tmp.component';
import { DenominationsComponent } from './denominations/denominations.component';

const routes: Routes = [
  {
    path: '',
    children: [
      {
        path: '',
        redirectTo: 'membership-cards',
        pathMatch: 'full',
      },
      {
        path: 'membership-cards',
        component: MembershipCardsComponent
      },
      {
        path: 'membership-type-cards',
        component: MembershipTypeCardsComponent
      },
      {
        path: 'denominations',
        component: DenominationsComponent
      },
      {
        path: 'blank-cards',
        component: BlankCardsComponent
      },
      {
        path: 'memberships-tmp',
        component: MembershipsTmpComponent
      }
    ]
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class MembershipsRoutingModule { }
