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


@Injectable()
export class ManagerLayoutService extends BaseService {
  constructor(
    config: ApiConfiguration,
    http: HttpClient
  ) {
    super(config, http);
  }

  /**
   */
  listPermissionsByRoleAndCompanyIdResponse(): Observable<HttpResponse<Permission[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/managerLayout/permissions`,
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
   */
  listPermissionsByRoleAndCompanyId(): Observable<Permission[]> {
    return this.listPermissionsByRoleAndCompanyIdResponse().pipe(
      map(_r => _r.body)
    );
  }}

export module ManagerLayoutService {
}
