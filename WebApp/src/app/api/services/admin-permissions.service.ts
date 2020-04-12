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

import { Permission } from '../models/permission';
import { PermissionForm } from '../models/permission-form';


@Injectable()
export class AdminPermissionsService extends BaseService {
  constructor(
    config: ApiConfiguration,
    http: HttpClient
  ) {
    super(config, http);
  }

  /**
   * @param page - The number of items to skip before starting to collect the result set.
   * @param limit - The numbers of items to return.
   */
  listPermissionsResponse(params: AdminPermissionsService.ListPermissionsParams): Observable<HttpResponse<Permission[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    if (params.page != null) __params = __params.set("page", params.page.toString());
    if (params.limit != null) __params = __params.set("limit", params.limit.toString());
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/admin/permissions`,
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
   * @param page - The number of items to skip before starting to collect the result set.
   * @param limit - The numbers of items to return.
   */
  listPermissions(params: AdminPermissionsService.ListPermissionsParams): Observable<Permission[]> {
    return this.listPermissionsResponse(params).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  createPermissionResponse(body?: PermissionForm): Observable<HttpResponse<Permission>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/admin/permissions`,
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
        let _body: Permission = null;
        _body = _resp.body as Permission
        return _resp.clone({body: _body}) as HttpResponse<Permission>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  createPermission(body?: PermissionForm): Observable<Permission> {
    return this.createPermissionResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  updatePermissionResponse(body?: PermissionForm): Observable<HttpResponse<Permission>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "PATCH",
      this.rootUrl + `/admin/permissions`,
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
        let _body: Permission = null;
        _body = _resp.body as Permission
        return _resp.clone({body: _body}) as HttpResponse<Permission>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  updatePermission(body?: PermissionForm): Observable<Permission> {
    return this.updatePermissionResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param permissionId - undefined
   */
  getPermissionByIdResponse(permissionId: number): Observable<HttpResponse<Permission>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/admin/permissions/${permissionId}`,
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
        let _body: Permission = null;
        _body = _resp.body as Permission
        return _resp.clone({body: _body}) as HttpResponse<Permission>;
      })
    );
  }

  /**
   * @param permissionId - undefined
   */
  getPermissionById(permissionId: number): Observable<Permission> {
    return this.getPermissionByIdResponse(permissionId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param permissionId - undefined
   */
  deletePermissionResponse(permissionId: number): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "DELETE",
      this.rootUrl + `/admin/permissions/${permissionId}`,
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
   * @param permissionId - undefined
   */
  deletePermission(permissionId: number): Observable<void> {
    return this.deletePermissionResponse(permissionId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  searchPermissionsResponse(body?: PermissionForm): Observable<HttpResponse<Permission[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/admin/permissions/search`,
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
   * @param body - undefined
   */
  searchPermissions(body?: PermissionForm): Observable<Permission[]> {
    return this.searchPermissionsResponse(body).pipe(
      map(_r => _r.body)
    );
  }}

export module AdminPermissionsService {
  export interface ListPermissionsParams {
    page?: number;
    limit?: number;
  }
}
