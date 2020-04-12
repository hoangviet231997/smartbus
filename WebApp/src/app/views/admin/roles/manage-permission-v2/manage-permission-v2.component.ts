import { Component, OnInit, ViewChild, AfterViewInit } from '@angular/core';
import { ModalDirective } from 'ngx-bootstrap/modal';
import swal from 'sweetalert2';
import { Router } from '@angular/router';
import { HttpErrorResponse } from '@angular/common/http';
import { TranslateService } from '@ngx-translate/core';
import { NgxSpinnerService } from 'ngx-spinner';
import { AdminCategoriesService, AdminCompaniesService, AdminRolesService, AdminPermissionsV2Service } from '../../../../api/services';
import { sp } from '@angular/core/src/render3';
import { locateHostElement } from '@angular/core/src/render3/instructions';
import { PermissionForm_v2 } from 'src/app/api/models';

@Component({
  selector: 'app-manage-permission-v2',
  templateUrl: './manage-permission-v2.component.html',
  styleUrls: ['./manage-permission-v2.component.css']
})
export class ManagePermissionV2Component implements OnInit, AfterViewInit {

  @ViewChild('addModal') public addModal: ModalDirective;
  @ViewChild('editModal') public editModal: ModalDirective;
  @ViewChild('detailModal') public detailModal: ModalDirective;

  public permissionCreate: PermissionForm_v2;
  public permissionUpdate: PermissionForm_v2;

  public categories: any= [];
  public companyIdSearch = 0;
  public cate_id = 0;
  public permissions: any = [];
  public view_advance: boolean;
  public edit: boolean;
  public isChecked: boolean;

  public masterSelected: boolean = false;
  public companies: any = [];
  public roles: any = [];
  public keyTools: any;
  public isCreated = false;

  public checkedListCreate: any = [];
  public checkedListUpdate: any = [];
  public permissionDatail: any = [];

  public isMasterCheck =  0;

  constructor(
    private ApiCategories: AdminCategoriesService,
    private apiCompanies: AdminCompaniesService,
    private apiRoles: AdminRolesService,
    private translate: TranslateService,
    private apiPermissionV2: AdminPermissionsV2Service,
    private spinner: NgxSpinnerService
  ){
    this.permissionCreate = new PermissionForm_v2();
    this.permissionUpdate = new PermissionForm_v2();
  }

  ngOnInit() {
    this.ApiCategories.listCategory({
        page: 1,
        limit: 999999
    }).subscribe((data) => {
      this.categories = [];
      data.forEach(element => {
        element['key_tools'] = [];
        this.categories.push(element);
      });
    });

    this.apiCompanies.listCompanies({
      page: 0,
      limit: 9999
    }).subscribe(
      companies => {
        this.companies = companies;
      }
    );

    this.apiRoles.listPermissionRoles().subscribe(
      res => {
        this.roles = res;
      }
    );
  }

  ngAfterViewInit() {
    this.masterSelected = false;
  }

  refreshView(){
    this.searchPermission();
  }

  searchPermission() {
    this.spinner.show();
    if (this.companyIdSearch > 0) {
      this.apiPermissionV2.searchPermissionsV2({
        company_id: this.companyIdSearch
      }).subscribe((res) => {
        this.permissions = res;
        this.spinner.hide();
      });
    }
  }

  getCheckedItemList() {

    this.checkedListCreate = [];
    this.categories.map(e_cate => {
      e_cate.isSelected = false;
      this.checkedListCreate.push({
        id: e_cate['id'] * (-1),
        key: e_cate['key'],
        key_tools: []
      });
    });
  }

  onChangeAllKeyCategory(event, type) {
    if(type == 0){
      this.checkedListCreate = [];
      if (event.target.checked) {
        this.categories.map((e, i) => {
          e.isSelected = this.masterSelected;
          this.checkedListCreate.push({
            id: e['id'] * (1),
            key: e['key'],
            key_tools: []
          });
        });
      } else {
        this.categories.map((e, i) => {
          e.isSelected = this.masterSelected;
          this.checkedListCreate.push({
            id: e['id'] * (-1),
            key: e['key'],
            key_tools: []
          });
        });
      }
    } else if(type == 1){
      if (event.target.checked) {
        this.categories.map((e, i) => {
          e.isSelected = this.masterSelected;
          this.checkedListUpdate.map((e_list_up) =>{
            if(e_list_up.id < 0)  e_list_up.id *= (-1);
          });
        });
      } else {
        this.categories.map((e, i) => {
          e.isSelected = this.masterSelected;
          e.key_tools = [];
        });
        this.checkedListUpdate.map((e_list_up, i) => {
          e_list_up.id *= (-1);
          e_list_up.key_tools = [];
        });
      }
    }
  }

  onChangeKeyCategory(event, type) {

    if(type == 0){
      const index_categories = this.categories.findIndex(e => (e.key === event.target.value));
      const index_checkedListCreate = this.checkedListCreate.findIndex(e => (e.key === event.target.value));

      if (event.target.checked) {
        this.categories.map((e, i) => {
          if (index_categories === i) e.isSelected = true;
        });
        this.checkedListCreate.map((ele, i) => {
          if (index_checkedListCreate === i) {
            ele.id *= (-1);
            ele.key = ele.key;
            ele.key_tools = [];
          }
        });
      } else {
        this.masterSelected = false;
        this.categories.map((e, i) => {
          if (index_categories === i) e.isSelected = false;
        });
        this.checkedListCreate.map((ele, i) => {
          if (index_checkedListCreate === i) {
            ele.id *= (-1);
            ele.key = ele.key;
            ele.key_tools = [];
          }
        });
      }
      this.masterSelected = this.categories.every(i => {
        return i.isSelected === true;
      });

    } else if(type == 1){

      const index_categories = this.categories.findIndex(e => (e.key === event.target.value));
      const index_checkedListUpdate = this.checkedListUpdate.findIndex(e => (e.key === event.target.value));

      if (event.target.checked) {
        this.categories.map((e, i) => {
          if (index_categories === i) e.isSelected = true;
        });
        this.checkedListUpdate.map((ele, i) => {
          if (index_checkedListUpdate === i) {
            ele.id *= (-1);
            ele.key = ele.key;
            ele.key_tools = [];
          }
        });
      } else {

        this.masterSelected = false;
        this.categories.map((e, i) => {
          if (index_categories === i) {
            e.isSelected = false;
            e.key_tools = [];
          }
        });
        this.checkedListUpdate.map((ele, i) => {
          if (index_checkedListUpdate === i) {
            ele.id *= (-1);
            ele.key = ele.key;
            ele.key_tools = [];
          }
        });
      }
      this.masterSelected = this.categories.every(i => {
        return i.isSelected === true;
      });
    }
  }

  onChangeCheckKeyTools(event, key: any, type) {

    if(type == 0){
      if (event.target.checked) {
        for (let index = 0; index < this.checkedListCreate.length; index++) {
          if ((this.checkedListCreate[index].key === key)) {
            this.checkedListCreate[index].key_tools.push(event.target.value);
          }
        }
      } else {
        if (event.target.value === 'view_advanced') {
          this.checkedListCreate.map(e => {
            if (e.key === key) {
              for (let i = 0; i < e.key_tools.length; i++) {
                if (e.key_tools[i] === 'view_advanced') {
                  e.key_tools.splice(i, 1);
                }
              }
            }
          });
        }

        if (event.target.value === 'edit') {
          this.checkedListCreate.map(e => {
            if (e.key === key) {
              for (let i = 0; i < e.key_tools.length; i++) {
                if (e.key_tools[i] === 'edit') {
                  e.key_tools.splice(i, 1);
                }
              }
            }
          });
        }
      }
    }else if(type == 1){

      if (event.target.checked) {
        for (let index = 0; index < this.checkedListUpdate.length; index++) {
          if ((this.checkedListUpdate[index].key === key)) {
            this.checkedListUpdate[index].key_tools.push(event.target.value);
          }
        }
      } else {
        if (event.target.value === 'view_advanced') {
          this.checkedListUpdate.map(e => {
            if (e.key === key) {
              for (let i = 0; i < e.key_tools.length; i++) {
                if (e.key_tools[i] === 'view_advanced') {
                  e.key_tools.splice(i, 1);
                }
              }
            }
          });
        }
        if (event.target.value === 'edit') {
          this.checkedListUpdate.map(e => {
            if (e.key === key) {
              for (let i = 0; i < e.key_tools.length; i++) {
                if (e.key_tools[i] === 'edit') {
                  e.key_tools.splice(i, 1);
                }
              }
            }
          });
        }
      }
    }
  }

  showAddModal() {
    this.addModal.show();
    this.masterSelected = false;
    this.permissionCreate.role_id = 0;
    this.permissionCreate.company_id = 0;
    this.getCheckedItemList();
  }

  addPermission() {

    if (this.permissionCreate.company_id <= 0 ) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_COMPANY'), 'warning');
      return;
    }

    if (this.permissionCreate.role_id <= 0) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_ROLE'), 'warning');
      return;
    }

    let index_list = this.checkedListCreate.findIndex(e => (e.id > 0));
    if (index_list == -1) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_ROLE_PERMISSION'), 'warning');
      return;
    }

    this.isCreated = true;
    this.spinner.show();
    this.apiPermissionV2.createPermissionV2({
      role_id: this.permissionCreate.role_id,
      company_id: this.permissionCreate.company_id,
      permission_data: this.checkedListCreate.length > 0 ? JSON.stringify(this.checkedListCreate) : null
    }).subscribe(() => {
      swal(this.translate.instant('SWAL_CREATE_SUCCESS'), '', 'success');
      this.isCreated = false;
      this.addModal.hide();
      this.companyIdSearch = this.permissionCreate.company_id;
      this.refreshView();
      this.spinner.hide();
    },
    err => {

      if (err instanceof HttpErrorResponse) {
        if (err.status === 404) {
          swal({ type: 'error', title: this.translate.instant('SWAL_ERROR'), text: err.error });
        } else if (err.status === 422) {
          swal({ type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_UPDATE_FAILD') });
        }
      }
      this.spinner.hide();
    });
  }

  showModalEdit(data) {

    this.checkedListUpdate = [];
    this.isMasterCheck = 1;
    this.spinner.show();
    this.apiPermissionV2.getPermissionV2ByRoleIdAndCompanyId({
      roleId: data.role_id,
      companyId: data.company_id
    }).subscribe((res) => {

      this.permissionUpdate.role_id = res.role_id;
      this.permissionUpdate.company_id = res.company_id;

      this.editModal.show();
      var arr = [];
      if(res.permission_data != null) arr = JSON.parse(res.permission_data);
      this.categories.map(e_cate => {
        e_cate.isSelected = false;
        e_cate.key_tools = [];
        if(arr.length > 0){
          const index_cate = arr.findIndex( (e) => (e.key == e_cate.key));
          if(index_cate != -1){
            e_cate.isSelected = true;
            e_cate.key_tools = arr[index_cate]['key_tools'];
            this.checkedListUpdate.push({
              id: arr[index_cate]['id'],
              key: arr[index_cate]['key'],
              key_tools: arr[index_cate]['key_tools'],
              status: 0
            });
          }else{
            this.checkedListUpdate.push({
              id: e_cate['id']* (-1),
              key: e_cate['key'],
              key_tools: [],
              status: 1
            });
          }
        }
      });
      this.spinner.hide();
    });
  }

  editPermission() {

    if (this.permissionUpdate.company_id <= 0) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_COMPANY'), 'warning');
      return;
    }

    if (this.permissionUpdate.role_id <= 0) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_ROLE'), 'warning');
      return;
    }

    let index_list = this.checkedListUpdate.findIndex(e => (e.id > 0));
    if (index_list == -1) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_ROLE_PERMISSION'), 'warning');
      return;
    }

    this.spinner.show();
    this.apiPermissionV2.updatePermissionV2({
      company_id: this.permissionUpdate.company_id,
      role_id: this.permissionUpdate.role_id,
      permission_data: JSON.stringify(this.checkedListUpdate)
    }).subscribe((res) => {
      this.editModal.hide();
      swal(this.translate.instant('SWAL_UPDATE_SUCCESS'), '', 'success');
      this.companyIdSearch = this.permissionUpdate.company_id;
      this.refreshView();
      this.spinner.hide();
    },
      err => {
        if (err instanceof HttpErrorResponse) {
          if (err.status === 404) {
            swal({ type: 'error', title: this.translate.instant('SWAL_ERROR'), text: err.error });
          } else if (err.status === 422) {
            swal({ type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_UPDATE_FAILD') });
          }
        }
        this.spinner.hide();
      }
    );
  }

  showDetailPermisionByRole(data){
    this.detailModal.show();
    this.permissionDatail = data.permission_data;
  }

  deletePermisionByRole(data){
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
        this.apiPermissionV2.deletePermissionV2ByRoleIdAndCompanyId({
          roleId: data.role_id,
          companyId: data.company_id
        }).subscribe(() => {
          this.spinner.hide();
          this.refreshView();
          swal(this.translate.instant('SWAL_DEL_SUCCESS'), '', 'success');

        });
      }
    });
  }
}
