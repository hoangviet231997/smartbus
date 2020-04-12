import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { NewsRoutingModule } from './news-routing.module';
import { NewsComponent } from './news/news.component';
import { CategoryNewsComponent } from './category-news/category-news.component';
import { SharedModule } from '../../../shared/shared.module';
import { ModalModule, PaginationModule, BsDatepickerModule, TimepickerModule } from 'ngx-bootstrap';
import { FormsModule } from '@angular/forms';
import { LaddaModule } from 'angular2-ladda';
import { SelectModule } from 'ng2-select';
// import { CKEditorModule } from '@ckeditor/ckeditor5-angular';



@NgModule({
  imports: [
    CommonModule,
    NewsRoutingModule,
    SharedModule,
    ModalModule,
    TimepickerModule.forRoot(),
    PaginationModule.forRoot(),
    FormsModule,
    LaddaModule.forRoot({
      style: 'slide-left',
    }),
    SelectModule,
    BsDatepickerModule.forRoot(),
    SharedModule
  ],
  declarations: [NewsComponent, CategoryNewsComponent]
})
export class NewsModule {}
