import { Injectable } from '@angular/core';
import { Permission } from '../api/models';

@Injectable({
  providedIn: 'root'
})
export class RolesService {
  permissions: Permission[];

  constructor() {
    if (localStorage.getItem('permissions')) {
      this.permissions = JSON.parse(localStorage.getItem('permissions'));
    }
  }

  setPermissions(permissions: Permission[]) {
    localStorage.setItem('permissions', JSON.stringify(permissions));
  }

  isAuthorized(permission: string) {
    return this.permissions.find(perm => perm.key === permission) !== undefined;
  }
}
