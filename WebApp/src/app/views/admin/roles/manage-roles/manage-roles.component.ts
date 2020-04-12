import { Component, OnInit, ViewChild, AfterViewInit, Pipe, PipeTransform } from '@angular/core';
import { ModalDirective } from 'ngx-bootstrap/modal';
import swal from 'sweetalert2';
import { AdminRolesService, AdminPermissionsService } from '../../../../api/services';
import { Router } from '@angular/router';
import { Role, RoleForm, Permission } from '../../../../api/models';
import { HttpErrorResponse } from '@angular/common/http';
import { TranslateService } from '@ngx-translate/core';
import { NgxSpinnerService } from 'ngx-spinner';

@Pipe({ name: 'filter' })
export class FilterPipe implements PipeTransform {
  public transform(values: Permission[], filter: string): any[] {
    if (!values || !values.length) {
      return [];
    }
    if (!filter) {
      return values;
    }

    return values.filter(v => v.key.toLowerCase().indexOf(filter.toLowerCase()) >= 0);
  }
}

@Component({
  selector: 'app-manage-roles',
  templateUrl: './manage-roles.component.html',
  styleUrls: ['./manage-roles.component.css']
})
export class ManageRolesComponent implements OnInit, AfterViewInit {

  @ViewChild('addModal') public addModal: ModalDirective;
  @ViewChild('editModal') public editModal: ModalDirective;
  @ViewChild('PermissionModal') public PermissionModal: ModalDirective;

  public roles: Role[];
  public role: Role;
  public roleForm: RoleForm;
  public permissions: Permission[];
  public selectedPermissions: number[] = [];
  public isCreated = false;
  public isUpdated = false;
  public isUpdatePers = false;
  public searchText: String;
  public roleId: number;
  public limitPage = 999999;

  constructor(
    private apiRoles: AdminRolesService, private apiPermissions: AdminPermissionsService, private router: Router,
    private translate: TranslateService, private spinner: NgxSpinnerService
  ) {
    this.role = new Role();
    this.roleForm = new RoleForm();
  }

  ngOnInit() {
  }

  ngAfterViewInit() {
    this.refreshView();
  }

  refreshView() {
    this.apiRoles.listRoles().subscribe(
      roles => {
        this.roles = roles;
      }
    );

    this.apiPermissions.listPermissions({
      limit: this.limitPage,
      page: 1
    }).subscribe(
      permissions => {
        this.permissions = permissions;
      }
    );
  }

  showAddRoleModal() {
    this.addModal.show();
  }

  showEditRoleModal(id: number) {
    this.spinner.show();
    this.apiRoles.getRoleById(id).subscribe(
      role => {
        this.role.id = role.id;
        this.role.name = role.name;
        this.role.display_name = role.display_name;
        this.spinner.hide();
        this.editModal.show();
      },
      err => {
        this.spinner.hide();
        swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_ERROR_NO_DATA')});
      }
    );
  }

  showPermissionModal(roleId: number) {
    this.spinner.show();
    this.searchText = '';
    this.selectedPermissions = [];
    this.apiRoles.getPermissionsByRoleId(roleId).subscribe(
      permissions => {
        for (let index = 0; index < permissions.length; index++) {
          const element = permissions[index];
          this.selectedPermissions.push(element.id);
        }
        this.spinner.hide();
        this.PermissionModal.show();
      },
      err => {
        this.spinner.hide();
        swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_ERROR_NO_DATA')});
      }
    );
    this.roleId = roleId;
  }

  addRole() {
    if (!this.role.name) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_NAME'), 'warning');
      return;
    }

    if (!this.role.display_name) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_DISPLAY_NAME'), 'warning');
      return;
    }

    this.isCreated = false;
    this.apiRoles.createRole({
      name: this.role.name,
      display_name: this.role.display_name
    }).subscribe(
      res => {
        this.addModal.hide();
        this.isCreated = false;
        this.role = new Role();
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

  editRole() {
    if (!this.role.name) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_NAME'), 'warning');
      return;
    }

    if (!this.role.display_name) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_DISPLAY_NAME'), 'warning');
      return;
    }

    this.isUpdated = false;
    this.apiRoles.updateRole({
      id: this.role.id,
      display_name: this.role.display_name
    }).subscribe(
      res => {
        this.editModal.hide();
        this.isUpdated = false;
        this.role = new Role();
        this.refreshView();
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

  deleteRole(id: number) {
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
        this.apiRoles.deleteRole(id).subscribe(
          res => {
            this.refreshView();
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

  savePermission() {
    this.isUpdatePers = false;
    this.apiRoles.assignPermissionToRoleId({
      roleId: this.roleId,
      body: this.selectedPermissions
    }).subscribe(
      res => {
        this.PermissionModal.hide();
        this.isUpdatePers = false;
        this.refreshView();
        this.selectedPermissions = [];
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
        this.isUpdatePers = false;
      }
    );
  }

  onPermissionChanged(event, permissionId) {
    if (event.currentTarget.checked) {
      this.selectedPermissions.push(permissionId);
    } else {
        const index: number =  this.selectedPermissions.indexOf(permissionId);
        if (index !== -1) {
          this.selectedPermissions.splice(index, 1);
        }
    }
  }

  checkAllPermission() {
    this.permissions.forEach(element => {
      if (this.searchText.length === 0 || element.key.toLowerCase().indexOf(this.searchText.toLowerCase()) >= 0) {
        if (this.selectedPermissions.indexOf(element.id) === -1) {
          this.selectedPermissions.push(element.id);
        }
      }
    });
  }
}
