import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { SharedRoutingModule } from './shared-routing.module';
import { AppHeaderComponent } from './app-header/app-header.component';
import { ModalModule, BsDatepickerModule } from 'ngx-bootstrap';
import { LaddaModule } from 'angular2-ladda';
import { FormsModule } from '@angular/forms';
import { RolesService } from './roles.service';
import { QtSocketService } from './qt-socket.service';
import { TranslateModule, TranslateLoader } from '@ngx-translate/core';
import { HttpClient } from '@angular/common/http';
import { TranslateHttpLoader } from '@ngx-translate/http-loader';



@NgModule({
  imports: [
    CommonModule,
    SharedRoutingModule,
    ModalModule,
    LaddaModule.forRoot({
      style: 'slide-left',
    }),
    FormsModule,
    BsDatepickerModule.forRoot(),
    TranslateModule
  ],
  declarations: [
    AppHeaderComponent,
  ],
  providers: [
    RolesService,
    QtSocketService
  ],
  exports: [
    AppHeaderComponent,
    TranslateModule
  ]
})
export class SharedModule { }
