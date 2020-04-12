import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { RoutesFormComponent } from './routes-form/routes-form.component';
import { RoutesListComponent } from './routes-list/routes-list.component';
import { GroupBusStationsComponent } from './group-bus-stations/group-bus-stations.component';

const routes: Routes = [
  {
    path: '',
    children: [
      {
        path: 'routes-list',
        component: RoutesListComponent
      },
      {
        path: 'routes-form',
        component: RoutesFormComponent
      },
      {
        path: 'group-bus-stations',
        component: GroupBusStationsComponent
      },
    ]
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class RoutesRoutingModule { }
