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

import { Permission_v2 } from '../models/permission-_v-2';
import { PermissionForm_v2 } from '../models/permission-form-_v-2';


@Injectable()
export class AdminPermissionsV2Service extends BaseService {
  constructor(
    config: ApiConfiguration,
    http: HttpClient
  ) {
    super(config, http);
  }

  /**
   * @param body - undefined
   */
  createPermissionV2Response(body?: PermissionForm_v2): Observable<HttpResponse<Permission_v2>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/admin/permissions_v2`,
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
        let _body: Permission_v2 = null;
        _body = _resp.body as Permission_v2
        return _resp.clone({body: _body}) as HttpResponse<Permission_v2>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  createPermissionV2(body?: PermissionForm_v2): Observable<Permission_v2> {
    return this.createPermissionV2Response(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  updatePermissionV2Response(body?: PermissionForm_v2): Observable<HttpResponse<Permission_v2>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "PATCH",
      this.rootUrl + `/admin/permissions_v2`,
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
        let _body: Permission_v2 = null;
        _body = _resp.body as Permission_v2
        return _resp.clone({body: _body}) as HttpResponse<Permission_v2>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  updatePermissionV2(body?: PermissionForm_v2): Observable<Permission_v2> {
    return this.updatePermissionV2Response(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param roleId - undefined
   * @param companyId - undefined
   */
  getPermissionV2ByRoleIdAndCompanyIdResponse(params: AdminPermissionsV2Service.GetPermissionV2ByRoleIdAndCompanyIdParams): Observable<HttpResponse<Permission_v2>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/admin/permissions_v2/${params.roleId}/${params.companyId}`,
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
        let _body: Permission_v2 = null;
        _body = _resp.body as Permission_v2
        return _resp.clone({body: _body}) as HttpResponse<Permission_v2>;
      })
    );
  }

  /**
   * @param roleId - undefined
   * @param companyId - undefined
   */
  getPermissionV2ByRoleIdAndCompanyId(params: AdminPermissionsV2Service.GetPermissionV2ByRoleIdAndCompanyIdParams): Observable<Permission_v2> {
    return this.getPermissionV2ByRoleIdAndCompanyIdResponse(params).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param roleId - undefined
   * @param companyId - undefined
   */
  deletePermissionV2ByRoleIdAndCompanyIdResponse(params: AdminPermissionsV2Service.DeletePermissionV2ByRoleIdAndCompanyIdParams): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    
    let req = new HttpRequest<any>(
      "DELETE",
      this.rootUrl + `/admin/permissions_v2/${params.roleId}/${params.companyId}`,
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
   * @param companyId - undefined
   */
  deletePermissionV2ByRoleIdAndCompanyId(params: AdminPermissionsV2Service.DeletePermissionV2ByRoleIdAndCompanyIdParams): Observable<void> {
    return this.deletePermissionV2ByRoleIdAndCompanyIdResponse(params).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  searchPermissionsV2Response(body?: PermissionForm_v2): Observable<HttpResponse<Permission_v2[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/admin/permissions_v2/search`,
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
        let _body: Permission_v2[] = null;
        _body = _resp.body as Permission_v2[]
        return _resp.clone({body: _body}) as HttpResponse<Permission_v2[]>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  searchPermissionsV2(body?: PermissionForm_v2): Observable<Permission_v2[]> {
    return this.searchPermissionsV2Response(body).pipe(
      map(_r => _r.body)
    );
  }}

export module AdminPermissionsV2Service {
  export interface GetPermissionV2ByRoleIdAndCompanyIdParams {
    roleId: number;
    companyId: number;
  }
  export interface DeletePermissionV2ByRoleIdAndCompanyIdParams {
    roleId: number;
    companyId: number;
  }
}
