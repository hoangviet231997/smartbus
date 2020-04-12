import { Component, OnInit, ViewChild, AfterViewInit, ViewEncapsulation } from '@angular/core';
import { ModalDirective } from 'ngx-bootstrap/modal';
import swal from 'sweetalert2';
import { Router } from '@angular/router';
import { AdminPermissionsService, AdminRolesService, AdminCompaniesService } from '../../../../api/services';
import { Permission, PermissionForm } from '../../../../api/models';
import { HttpErrorResponse } from '@angular/common/http';
import { map } from 'rxjs/operators/map';
import { TranslateService } from '@ngx-translate/core';
import { NgxSpinnerService } from 'ngx-spinner';

@Component({
  selector: 'app-manage-permissions',
  templateUrl: './manage-permissions.component.html',
  styleUrls: ['./manage-permissions.component.css'],
  encapsulation: ViewEncapsulation.None
})
export class ManagePermissionsComponent implements OnInit, AfterViewInit {

  @ViewChild('addModal') public addModal: ModalDirective;
  @ViewChild('editModal') public editModal: ModalDirective;

  public permissions: Permission[] = [];
  public permission: Permission;
  public permissionForm: PermissionForm;
  public isCreated = false;
  public isUpdated = false;
  public limitPage = 10;
  public currentPage = 1;
  public paginationTotal;
  public paginationCurrent;
  public paginationLast;
  public roles: any[];
  public role_id: any;
  public page_id: any;
  // public edit_accept: any = 0;
  public keyTools: any = [];
  public roleIdSearch: any;
  public statusSearch: any = false;
  public loadDefault: any = false;

  public companyItems: Array<any> = [];
  public valueSelectedCompany: any = {};
  public companyIdSearch = 0;
  public companies = [];
  public company_id = 0;
  public companies_id = 0;
  public company_name: any;

  constructor(
    private apiPermissions: AdminPermissionsService, private router: Router, private translate: TranslateService,
    private spinner: NgxSpinnerService, private apiRoles: AdminRolesService, private apiCompanies: AdminCompaniesService
  ) {
    this.permission = new Permission();
    this.permissionForm = new PermissionForm();
    this.valueSelectedCompany = {};
    this.companyIdSearch = 0;
  }

  ngOnInit() {
    this.getRoles();
    this.roleIdSearch = 0;

    this.apiCompanies.listCompanies({
      page: 0,
      limit: 9999
    }).subscribe(
      companies => {
        this.companies = companies;
        this.companyItems = [];
        for (let i = 0; i < companies.length; i++) {
          this.companyItems.push({
            id: companies[i].id,
            text: companies[i].name
          });
        }
      }
    );
  }

  ngAfterViewInit() {
    this.refreshView();
  }

  refreshView() {

    this.apiPermissions.listPermissionsResponse({
      page: this.currentPage,
      limit: this.limitPage
    }).pipe(
      map(_r => {
        return _r;
      }
      )).subscribe(
        resp => {
          this.loadDefault = true;
          this.permissions = resp.body['data'];
          this.paginationTotal = resp.body['total'];
          this.paginationCurrent = resp.body['current_page'];
          this.paginationLast = resp.body['last_page'];

        }
      );

  }

  getRoles(){
    this.apiRoles.listPermissionRoles().subscribe(
      res => {
        this.roles = res;
      }
    );
  }

  pageChanged(event: any): void {
    this.currentPage = event.page;
    this.refreshView();
  }

  showAddPermissionModal() {
    this.permission = new Permission();
    this.keyTools = [];
    this.addModal.show();
  }

  showEditPermissionModal(id: number) {

    this.spinner.show();
    this.keyTools = [];
    this.apiPermissions.getPermissionById(id).subscribe(
      permission => {
        this.company_name = permission['company_name'];
        this.companies_id = permission['company_id'];
        this.permissionForm.id = permission.id;
        this.page_id = permission.key;
        // this.edit_accept = permission.edit;
        if(permission.key_tools !== null){
          this.keyTools = JSON.parse(permission.key_tools);
        }
        this.role_id = permission.role_id;
        this.spinner.hide();
        this.editModal.show();
      },
      err => {
        this.spinner.hide();
        swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_ERROR_NO_DATA')});
      }
    );
  }

  changeCheckedKeyTools(event, key_tools){
    if (event.currentTarget.checked){
      this.keyTools.push(key_tools)
    }else{
      const index: number = this.keyTools.indexOf(key_tools);
      if (index !== -1) {
        this.keyTools.splice(index, 1);
      }
    }
  }

  addPermission() {
    if (!this.role_id) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_PERMISION_ROLE'), 'warning');
      return;
    }

    if (!this.page_id) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_PERMISION_PAGE'), 'warning');
      return;
    }

    this.isCreated = false;
    this.apiPermissions.createPermission({
      role_id: this.role_id,
      key: this.page_id,
      company_id:this.company_id,
      key_tools: (this.keyTools.length > 0) ?  (JSON.stringify(this.keyTools)) : (null)
    }).subscribe(
      res => {
        this.addModal.hide();
        this.isCreated = false;
        this.permission = new Permission();
        this.role_id = 0;
        this.page_id = null;
        // this.edit_accept = 0;
        this.keyTools = [];
        if (this.statusSearch) {
          this.searchPermission();
        } else {
          this.refreshView();
        }
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

  editPermission() {
    if (!this.role_id) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_PERMISION_ROLE'), 'warning');
      return;
    }

    if (!this.page_id) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_PERMISION_PAGE'), 'warning');
      return;
    }

    this.isUpdated = false;
    this.apiPermissions.updatePermission({
      id: this.permissionForm.id,
      role_id: this.role_id,
      key: this.page_id,
      company_id: this.companies_id,
      key_tools: (this.keyTools.length > 0) ?  (JSON.stringify(this.keyTools)) : (null)
    }).subscribe(
      res => {
        this.editModal.hide();
        this.isUpdated = false;
        this.keyTools = [];
        this.permissionForm = new PermissionForm();
        if (this.statusSearch) {
          this.searchPermission();
        } else {
          this.refreshView();
        }
        swal(this.translate.instant('SWAL_UPDATE_SUCCESS'), '', 'success');
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

  deletePermission(id: number) {
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
        this.apiPermissions.deletePermission(id).subscribe(
          res => {
            if (this.statusSearch) {
              this.searchPermission();
            } else {
              this.refreshView();
            }
            this.spinner.hide();
            swal(this.translate.instant('SWAL_DEL_SUCCESS'), '', 'success');
          },
          err => {
            this.spinner.hide();
            if (err instanceof HttpErrorResponse) {
              if (err.status === 404) {
                swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: err.error});
              }
            }
          }
        );
      }
    });
  }

  searchPermission() {

    // if (this.companyIdSearch <= 0) {
    //   swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_PERMISION_COM'), 'warning');
    //   return;
    // }

    // if (this.roleIdSearch <= 0) {
    //   swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_PERMISION_ROLE'), 'warning');
    //   return;
    // }

    this.statusSearch = true;
    this.permissions = [];
    if (this.roleIdSearch != 0 || this.companyIdSearch != 0) {
      this.apiPermissions.searchPermissions({
        role_id: this.roleIdSearch,
        company_id: this.companyIdSearch
      }).subscribe(
        res => {
          this.loadDefault = true;
          this.permissions = res;
          this.paginationTotal = null;
        },
        err => {
          if (err instanceof HttpErrorResponse) {
            if (err.status === 404) {
              swal({ type: 'error', title: this.translate.instant('SWAL_ERROR'), text: err.error });
            } else if (err.status === 422) {
              swal({ type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_CREATE_FAILD') });
            }
          }
          this.isCreated = false;
        }
      );
    } else {
      this.loadDefault = false;
      this.refreshView();
    }
  }

  public selectedCompany(value:any):void {
    this.companyIdSearch = value.id;
    this.searchPermission();
  }

  public selectedAddCompany(value:any):void {
    this.company_id = value.id;
  }

  public removedCompany(value:any):void {
    this.companyIdSearch = 0;
    this.refreshView();
  }

  public refreshValueCompany(value:any):void {
    this.valueSelectedCompany = value;
  }
}
