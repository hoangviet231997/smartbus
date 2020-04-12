import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { UsersListComponent } from './users-list/users-list.component';
import { SharedModule } from '../../../shared/shared.module';
import { CleanupSocketGuard } from '../../../shared/cleanup-socket.guard';

const routes: Routes = [
  {
    path: '',
    children: [
      {
        path: 'users-list',
        canDeactivate: [CleanupSocketGuard],
        component: UsersListComponent
      },
    ]
  }
];

@NgModule({
  imports: [
    RouterModule.forChild(routes),
    SharedModule
  ],
  exports: [RouterModule]
})
export class UsersRoutingModule { }
