import { Component, OnInit, ViewChild, AfterViewInit } from '@angular/core';
import { ModalDirective } from 'ngx-bootstrap/modal';
import swal from 'sweetalert2';
import { HttpErrorResponse } from '@angular/common/http';
import { MouseEvent } from '@agm/core';
import { TranslateService } from '@ngx-translate/core';
import { NgxSpinnerService } from 'ngx-spinner';
import { ModuleAppForm } from '../../../api/models';
import { AdminModuleAppsService } from '../../../api/services';

@Component({
  selector: 'app-module-apps',
  templateUrl: './module-apps.component.html',
  styleUrls: ['./module-apps.component.css']
})
export class ModuleAppsComponent implements OnInit {

  @ViewChild('addModal') public addModal: ModalDirective;
  @ViewChild('editModal') public editModal: ModalDirective;

  // public isCreated = false;
  // public isUpdated = false;
  public module_apps: any = [];
  // public moduleAppCreate: ModuleAppForm;
  // public moduleAppUpdate: ModuleAppForm;

  constructor(
    private translate: TranslateService, 
    private spinner: NgxSpinnerService,
    private apiModuleApp: AdminModuleAppsService
  ) {
    // this.moduleAppCreate = new ModuleAppForm();
    // this.moduleAppUpdate = new ModuleAppForm();
  }

  ngOnInit() {
  }

  ngAfterViewInit() {
    this.refreshView();
  }

  refreshView() {

    this.apiModuleApp.listModuleApp().subscribe(
      data => {
        this.module_apps = data;
      }
    );
  }

  //-----------------------------------------------end------------------------------------//

  // ShowModuleAppAddModal(){
  //   this.addModal.show();
  // }

  // ShowModuleEditAddModal(id: number){

  //   this.spinner.show();
  //   this.apiModuleApp.getModuleAppById(id).subscribe(
  //     data => {
  //       this.moduleAppUpdate.id = data.id;
  //       this.moduleAppUpdate.name = data.name;
  //       this.moduleAppUpdate.display_name = data.display_name;
  //       this.moduleAppUpdate.description = data.description;
  //       this.spinner.hide();
  //       this.editModal.show();
  //     },
  //     err => {
  //       this.spinner.hide();
  //       swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_ERROR_MODEL')});
  //     }
  //   );
  // }

  // createModuleApp(){

  //   if (!this.moduleAppCreate.name) {
  //     swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_MODULE_APP_NAME'), 'warning');
  //     return;
  //   }

  //   if (!this.moduleAppCreate.display_name) {
  //     swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_MODULE_APP_DISPLAYNAME'), 'warning');
  //     return;
  //   } 
  //   this.isCreated = true;
  //   this.apiModuleApp.createModuleApp({
  //     name: this.moduleAppCreate.name,
  //     display_name: this.moduleAppCreate.display_name,
  //     description: this.moduleAppCreate.description
  //   }).subscribe(
  //     data => {
  //       this.addModal.hide();
  //       this.moduleAppCreate = new ModuleAppForm();
  //       this.moduleAppCreate.name = null;
  //       this.moduleAppCreate.display_name = null;
  //       this.moduleAppCreate.description = null;
  //       this.isCreated = false;
  //       swal(this.translate.instant('SWAL_CREATE_SUCCESS'), '', 'success');
  //       this.refreshView();
  //     },
  //     err => {if (err instanceof HttpErrorResponse) {
  //       if (err.status === 404) {
  //         swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: err.error});
  //       } else if (err.status === 422) {
  //         swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_UPDATE_FAILD')});
  //       }
  //     }
  //     this.isCreated = false;
  //     }
  //   );
  // }

  // // editModuleApp(){

  // //   if (!this.moduleAppUpdate.name) {
  // //     swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_MODULE_APP_NAME'), 'warning');
  // //     return;
  // //   }

  // //   if (!this.moduleAppUpdate.display_name) {
  // //     swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_MODULE_APP_DISPLAYNAME'), 'warning');
  // //     return;
  // //   } 

  // //   this.isUpdated = true;
  // //   this.apiModuleApp.updateModuleApp({
  // //     id: this.moduleAppUpdate.id,
  // //     name: this.moduleAppUpdate.name,
  // //     display_name: this.moduleAppUpdate.display_name,
  // //     description: this.moduleAppUpdate.description,
  // //   }).subscribe(
  // //     data => {
  // //       this.editModal.hide();
  // //       this.moduleAppUpdate =  new ModuleAppForm();
  // //       this.isUpdated = false;
  // //       this.refreshView();
  // //       swal(this.translate.instant('SWAL_UPDATE_SUCCESS'), '', 'success');
  // //     },
  // //     err => {
  // //       if (err instanceof HttpErrorResponse) {
  // //         if (err.status === 404) {
  // //           swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: err.error});
  // //         } else if (err.status === 422) {
  // //           swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_UPDATE_FAILD')});
  // //         }
  // //       }
  // //       this.isUpdated = false;
  // //     }
  // //   );

  // }
}
