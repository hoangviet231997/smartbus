import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { AppNotifiesComponent } from "./app-notifies/app-notifies.component";
import { WebNotifiesComponent } from './web-notifies/web-notifies.component';


const routes: Routes = [
  {
    path: "app-notifies",
    component: AppNotifiesComponent
  },
  {
    path: "web-notifies",
    component: WebNotifiesComponent
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class NotifiesRoutingModule { }
