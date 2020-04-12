import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { PartnersComponent } from './partners/partners.component';
import { PartnersAccountComponent } from './partners-account/partners-account.component';

const routes: Routes = [
  {
    path: '',
    children: [
      {
        path: 'partners',
        component: PartnersComponent
      },
      {
        path: 'partners-account',
        component: PartnersAccountComponent
      }
    ]
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class PartnersRoutingModule { }
