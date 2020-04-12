import { Component, OnInit, ViewChild, AfterViewInit } from '@angular/core';
import { ModalDirective } from 'ngx-bootstrap/modal';
import swal from 'sweetalert2';
import { HttpErrorResponse } from '@angular/common/http';
import { MouseEvent } from '@agm/core';
import { TranslateService } from '@ngx-translate/core';
import { NgxSpinnerService } from 'ngx-spinner';
import { ModuleAppForm } from '../../../api/models';
import { AdminModuleAppsService, ManagerModuleCompanyService } from '../../../api/services';
import { ActivityLogsService } from '../../../shared/activity-logs.service';

@Component({
  selector: 'app-module-apps',
  templateUrl: './module-apps.component.html',
  styleUrls: ['./module-apps.component.css']
})
export class ModuleAppsComponent implements OnInit {

  @ViewChild('addModal') public addModal: ModalDirective;

  public isCheckModule = false;
  public isCreated = false;
  public selectedModuleApp: number[] = [];
  public module_apps: any = [];
  public module_companies: any = [];

  public user_down: any = null;
  public permissions:any[] = [];

  constructor(
    private translate: TranslateService, 
    private spinner: NgxSpinnerService,
    private apiModuleApp: AdminModuleAppsService,
    private apiModuleCompanies: ManagerModuleCompanyService,
    private activityLogs: ActivityLogsService
  ) { }

  ngOnInit() {
    
    this.user_down = localStorage.getItem('token_shadow');
    if (localStorage.getItem('user')) {
      this.permissions = JSON.parse(localStorage.getItem('user')).permissions;
    }
  }

  ngAfterViewInit() {
    this.refreshView();
  }

  refreshView() {
    
    this.apiModuleCompanies.listModuleCompany().subscribe(data => {
      this.module_companies = data;
        
    });
  }

  ShowModuleAppAddModal(){

    this.addModal.show();
    this.apiModuleApp.listModuleApp().subscribe(data =>{
      this.module_apps = data;
    });
  }

  onModuleChange(event, module_id){

    if (event.currentTarget.checked) {

      this.selectedModuleApp.push(module_id);
    }else{

      const index: number = this.selectedModuleApp.indexOf(module_id);

      if (index !== -1) {
        this.selectedModuleApp.splice(index, 1);
      }
    }
  }

  createModuleAppCompany(){

    if(this.selectedModuleApp.length <= 0){
      swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_ERROR_MODULE_CHECK')});
      return;
    }
    
    this.isCreated = true
    this.apiModuleCompanies.createModuleCompany({
      modules: this.selectedModuleApp
    }).subscribe(data => {

        //call service create activity log
        var activity_log: any = [];
        activity_log['user_down'] =  this.user_down ? this.user_down : null;
        activity_log['action'] = 'create';
        activity_log['subject_type'] = 'module_app';
        activity_log['subject_data'] = this.selectedModuleApp ? JSON.stringify({
          modules: this.selectedModuleApp
        }) : '';
        var activityLog = this.activityLogs.createActivityLog(activity_log);

        this.addModal.hide();
        this.selectedModuleApp = [];
        this.isCreated = false;
        swal(this.translate.instant('SWAL_CREATE_SUCCESS'), '', 'success');
        this.refreshView();
      },
      err => {if (err instanceof HttpErrorResponse) {
        if (err.status === 404) {
          swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: err.error});
        } else if (err.status === 422) {
          swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_UPDATE_FAILD')});
        }
      }
      this.isCreated = false;
      }
    );
  }

  deleteModuleAppCompany(id: number){

    if(!id){
      swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_ERROR_MEMBER_ID')});
      return;
    }

    swal({
      title: this.translate.instant('SWAL_ERROR_SURE'),
      text: this.translate.instant('SWAL_ERROR_REMOVE'),
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: this.translate.instant('SWAL_OK'),
      cancelButtonText: this.translate.instant('SWAL_CANCEL')
    }).then((result) => {
      if (result.value) {
        this.spinner.show();
        this.apiModuleCompanies.managerDeleteModuleCompany(id).subscribe(
          res => {

            //call service create activity log
            var activity_log: any = [];
            activity_log['user_down'] =  this.user_down ? this.user_down : null;
            activity_log['action'] = 'delete';
            activity_log['subject_type'] = 'module_app';
            activity_log['subject_data'] = id ? JSON.stringify({
              id: id
            }) : '';
            var activityLog = this.activityLogs.createActivityLog(activity_log);

            this.refreshView();
            this.spinner.hide();
            swal(this.translate.instant('SWAL_DEL_SUCCESS'), '', 'success');
          },
          err => {
            this.spinner.hide();
            swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_DEL_FAILD')});
          }
        );
      }
    });

  }
}
