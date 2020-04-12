import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { TicketsComponent } from './tickets/tickets.component';
import { TicketProvidersComponent } from './ticket-providers/ticket-providers.component';
import { DenominationGoodsComponent } from './denomination-goods/denomination-goods.component';
import { DenominationServiceComponent } from './denomination-service/denomination-service.component';

const routes: Routes = [
  {
    path: '',
    children: [
      {
        path: '',
        redirectTo: 'tickets',
        pathMatch: 'full',
      },
      {
        path: 'tickets',
        component: TicketsComponent
      },
      {
        path: 'ticket-provicers',
        component: TicketProvidersComponent
      },
      {
        path: 'denomination-goods',
        component: DenominationGoodsComponent
      },
      {
        path: 'denomination-service',
        component: DenominationServiceComponent
      }
    ]
  }
]

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class TicketsRoutingModule { }
