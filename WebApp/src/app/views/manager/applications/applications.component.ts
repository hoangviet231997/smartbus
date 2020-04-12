import { Component, OnInit, AfterViewInit, ViewChild } from '@angular/core';
import { Application } from '../../../api/models';
import { ManagerAppsService } from '../../../api/services';
import { map } from 'rxjs/operators/map';
import { ModalDirective } from 'ngx-bootstrap/modal';
import swal from 'sweetalert2';
import { TranslateService } from '@ngx-translate/core';
import { HttpErrorResponse } from '@angular/common/http';
import { NgxSpinnerService } from 'ngx-spinner';

@Component({
  selector: 'app-applications',
  templateUrl: './applications.component.html',
  styleUrls: ['./applications.component.css']
})
export class ApplicationsComponent implements OnInit, AfterViewInit {

  @ViewChild('addModal') public addModal: ModalDirective;
  @ViewChild('editModal') public editModal: ModalDirective;

  public applications: Application[];
  public formCreateApp: Application;
  public formUpdateApp: Application;
  public isCreated = false;
  public isUpdated = false;

  public user_down: any = null ;

  public limitPage = 10;
  public currentPage = 1;
  public paginationTotal;
  public paginationCurrent;
  public paginationLast;
  public permissions:any[] = [];

  constructor(private apiApps: ManagerAppsService, private translate: TranslateService, private spinner: NgxSpinnerService) {
    this.formCreateApp = new Application();
    this.formUpdateApp = new Application();
  }

  ngOnInit() {
    if (localStorage.getItem('user')) {
      this.permissions = JSON.parse(localStorage.getItem('user')).permissions;
    }
  }

  ngAfterViewInit() {
    this.refreshView();
  }

  refreshView() {

    this.user_down = localStorage.getItem('token_shadow');

    this.apiApps.managerAppsListResponse({
      page: this.currentPage,
      limit: this.limitPage
    }).pipe(
      map(_r => {
        return _r;
      })
    ).subscribe(
      resp => {
        this.applications = resp.body;
        this.paginationTotal = resp.headers.get('pagination-total');
        this.paginationCurrent = resp.headers.get('pagination-current');
        this.paginationLast = resp.headers.get('pagination-last');
      }
    );
  }

  pageChanged(event: any): void {
    this.currentPage = event.page;
    this.refreshView();
  }

  showAddModal() {
    this.formCreateApp = new Application();
    this.addModal.show();
  }

  showEditModal(id: number) {
    this.spinner.show();
    this.apiApps.managerAppsGetById(id).subscribe(
      resp => {
        this.formUpdateApp.company_name = resp.company_name;
        this.formUpdateApp.company_address = resp.company_address;
        this.formUpdateApp.email = resp.email;
        this.formUpdateApp.url = resp.url;
        this.formUpdateApp.api_key = resp.api_key;
        this.formUpdateApp.id = resp.id;
        this.spinner.hide();
        this.editModal.show();
      },
      err => {
        this.spinner.hide();
        swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_ERROR_NO_DATA')});
      }
    );
  }

  createApp() {
    if (this.formCreateApp.company_name === undefined || !this.formCreateApp.company_name.trim()) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_COMPANY_NAME'), 'warning');
      return;
    }

    this.isCreated = true;
    this.apiApps.managerAppsCreate({
      company_name: this.formCreateApp.company_name,
      company_address: this.formCreateApp.company_address,
      email: this.formCreateApp.email,
      url: this.formCreateApp.url
    }).subscribe(
      resp => {
        this.addModal.hide();
        this.formCreateApp = new Application();
        this.isCreated = false;
        this.refreshView();
        swal(this.translate.instant('SWAL_CREATE_SUCCESS'), '', 'success');
      },
      err => {
        if (err instanceof HttpErrorResponse) {
          if (err.status === 404) {
            swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: err.error});
          } else if (err.status === 422) {
            swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_CREATE_FAILD')});
          }
        }
        this.isCreated = false;
      }
    );
  }

  updateApp() {
    if (this.formUpdateApp.company_name === undefined || !this.formUpdateApp.company_name.trim()) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_COMPANY_NAME'), 'warning');
      return;
    }

    this.isUpdated = true;
    this.apiApps.managerAppsUpdate({
      id: this.formUpdateApp.id,
      company_name: this.formUpdateApp.company_name,
      company_address: this.formUpdateApp.company_address,
      email: this.formUpdateApp.email,
      url: this.formUpdateApp.url
    }).subscribe(
      resp => {
        this.editModal.hide();
        this.formUpdateApp = new Application();
        this.isUpdated = true;
        this.refreshView();
      },
      err => {
        if (err instanceof HttpErrorResponse) {
          if (err.status === 404) {
            swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: err.error});
          } else if (err.status === 422) {
            swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_UPDATE_FAILD')});
          }
        }
        this.isUpdated = false;
      }
    );
  }

  deleteApp(id: number) {
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
        this.apiApps.managerAppsDelete(id).subscribe(
          res => {
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

  changeApiKey(appId: number) {
    swal({
      title: this.translate.instant('SWAL_ERROR_CHANGE_API_KEY'),
      text: '',
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: this.translate.instant('SWAL_OK'),
      cancelButtonText: this.translate.instant('SWAL_CANCEL')
    }).then((result) => {
      if (result.value) {
        this.spinner.show();
        this.apiApps.managerAppsChangeApiKeyById(appId).subscribe(
          resp => {
            this.refreshView();
            this.spinner.hide();
            swal(this.translate.instant('SWAL_UPDATE_SUCCESS'), '', 'success');
          },
          err => {
            this.spinner.hide();
            swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_UPDATE_FAILD')});
          }
        );
      }
    });
  }
}
