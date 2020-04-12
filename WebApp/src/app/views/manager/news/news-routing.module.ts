import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { CategoryNewsComponent } from "./category-news/category-news.component";
import { NewsComponent } from "./news/news.component";

const routes: Routes = [
  {
    path:'news',
    component: NewsComponent
  },
  {
    path:'category-news',
    component: CategoryNewsComponent
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class NewsRoutingModule { }
