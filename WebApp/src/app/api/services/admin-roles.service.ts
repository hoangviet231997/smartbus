/* tslint:disable */
import { Injectable } from '@angular/core';
import {
  HttpClient, HttpRequest, HttpResponse, 
  HttpHeaders, HttpParams } from '@angular/common/http';
import { BaseService } from '../base-service';
import { ApiConfiguration } from '../api-configuration';
import { Observable } from 'rxjs/Observable';
import { map } from 'rxjs/operators/map';
import { filter } from 'rxjs/operators/filter';

import { Role } from '../models/role';
import { RoleForm } from '../models/role-form';
import { Permission } from '../models/permission';


@Injectable()
export class AdminRolesService extends BaseService {
  constructor(
    config: ApiConfiguration,
    http: HttpClient
  ) {
    super(config, http);
  }

  /**
   */
  listRolesResponse(): Observable<HttpResponse<Role[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/admin/roles`,
      __body,
      {
        headers: __headers,
        params: __params,
        responseType: 'json'
      });

    return this.http.request<any>(req).pipe(
      filter(_r => _r instanceof HttpResponse),
      map(_r => {
        let _resp = _r as HttpResponse<any>;
        let _body: Role[] = null;
        _body = _resp.body as Role[]
        return _resp.clone({body: _body}) as HttpResponse<Role[]>;
      })
    );
  }

  /**
   */
  listRoles(): Observable<Role[]> {
    return this.listRolesResponse().pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  createRoleResponse(body?: RoleForm): Observable<HttpResponse<Role>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/admin/roles`,
      __body,
      {
        headers: __headers,
        params: __params,
        responseType: 'json'
      });

    return this.http.request<any>(req).pipe(
      filter(_r => _r instanceof HttpResponse),
      map(_r => {
        let _resp = _r as HttpResponse<any>;
        let _body: Role = null;
        _body = _resp.body as Role
        return _resp.clone({body: _body}) as HttpResponse<Role>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  createRole(body?: RoleForm): Observable<Role> {
    return this.createRoleResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  updateRoleResponse(body?: RoleForm): Observable<HttpResponse<Role>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "PATCH",
      this.rootUrl + `/admin/roles`,
      __body,
      {
        headers: __headers,
        params: __params,
        responseType: 'json'
      });

    return this.http.request<any>(req).pipe(
      filter(_r => _r instanceof HttpResponse),
      map(_r => {
        let _resp = _r as HttpResponse<any>;
        let _body: Role = null;
        _body = _resp.body as Role
        return _resp.clone({body: _body}) as HttpResponse<Role>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  updateRole(body?: RoleForm): Observable<Role> {
    return this.updateRoleResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   */
  listPermissionRolesResponse(): Observable<HttpResponse<Role[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/admin/roles/permission`,
      __body,
      {
        headers: __headers,
        params: __params,
        responseType: 'json'
      });

    return this.http.request<any>(req).pipe(
      filter(_r => _r instanceof HttpResponse),
      map(_r => {
        let _resp = _r as HttpResponse<any>;
        let _body: Role[] = null;
        _body = _resp.body as Role[]
        return _resp.clone({body: _body}) as HttpResponse<Role[]>;
      })
    );
  }

  /**
   */
  listPermissionRoles(): Observable<Role[]> {
    return this.listPermissionRolesResponse().pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param roleId - undefined
   */
  getRoleByIdResponse(roleId: number): Observable<HttpResponse<Role>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/admin/roles/${roleId}`,
      __body,
      {
        headers: __headers,
        params: __params,
        responseType: 'json'
      });

    return this.http.request<any>(req).pipe(
      filter(_r => _r instanceof HttpResponse),
      map(_r => {
        let _resp = _r as HttpResponse<any>;
        let _body: Role = null;
        _body = _resp.body as Role
        return _resp.clone({body: _body}) as HttpResponse<Role>;
      })
    );
  }

  /**
   * @param roleId - undefined
   */
  getRoleById(roleId: number): Observable<Role> {
    return this.getRoleByIdResponse(roleId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param roleId - undefined
   * @param body - undefined
   */
  assignPermissionToRoleIdResponse(params: AdminRolesService.AssignPermissionToRoleIdParams): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    __body = params.body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/admin/roles/${params.roleId}`,
      __body,
      {
        headers: __headers,
        params: __params,
        responseType: 'text'
      });

    return this.http.request<any>(req).pipe(
      filter(_r => _r instanceof HttpResponse),
      map(_r => {
        let _resp = _r as HttpResponse<any>;
        let _body: void = null;
        
        return _resp.clone({body: _body}) as HttpResponse<void>;
      })
    );
  }

  /**
   * @param roleId - undefined
   * @param body - undefined
   */
  assignPermissionToRoleId(params: AdminRolesService.AssignPermissionToRoleIdParams): Observable<void> {
    return this.assignPermissionToRoleIdResponse(params).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param roleId - undefined
   */
  deleteRoleResponse(roleId: number): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "DELETE",
      this.rootUrl + `/admin/roles/${roleId}`,
      __body,
      {
        headers: __headers,
        params: __params,
        responseType: 'text'
      });

    return this.http.request<any>(req).pipe(
      filter(_r => _r instanceof HttpResponse),
      map(_r => {
        let _resp = _r as HttpResponse<any>;
        let _body: void = null;
        
        return _resp.clone({body: _body}) as HttpResponse<void>;
      })
    );
  }

  /**
   * @param roleId - undefined
   */
  deleteRole(roleId: number): Observable<void> {
    return this.deleteRoleResponse(roleId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param roleId - undefined
   */
  getPermissionsByRoleIdResponse(roleId: number): Observable<HttpResponse<Permission[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/admin/roles/${roleId}/permissions`,
      __body,
      {
        headers: __headers,
        params: __params,
        responseType: 'json'
      });

    return this.http.request<any>(req).pipe(
      filter(_r => _r instanceof HttpResponse),
      map(_r => {
        let _resp = _r as HttpResponse<any>;
        let _body: Permission[] = null;
        _body = _resp.body as Permission[]
        return _resp.clone({body: _body}) as HttpResponse<Permission[]>;
      })
    );
  }

  /**
   * @param roleId - undefined
   */
  getPermissionsByRoleId(roleId: number): Observable<Permission[]> {
    return this.getPermissionsByRoleIdResponse(roleId).pipe(
      map(_r => _r.body)
    );
  }}

export module AdminRolesService {
  export interface AssignPermissionToRoleIdParams {
    roleId: number;
    body?: number[];
  }
}
