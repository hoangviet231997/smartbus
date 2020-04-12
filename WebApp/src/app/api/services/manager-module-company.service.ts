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

import { ModuleAppCompany } from '../models/module-app-company';
import { ModuleApp } from '../models/module-app';
import { ModuleIdArray } from '../models/module-id-array';


@Injectable()
export class ManagerModuleCompanyService extends BaseService {
  constructor(
    config: ApiConfiguration,
    http: HttpClient
  ) {
    super(config, http);
  }

  /**
   */
  listModuleCompanyResponse(): Observable<HttpResponse<ModuleAppCompany[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/module_company`,
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
        let _body: ModuleAppCompany[] = null;
        _body = _resp.body as ModuleAppCompany[]
        return _resp.clone({body: _body}) as HttpResponse<ModuleAppCompany[]>;
      })
    );
  }

  /**
   */
  listModuleCompany(): Observable<ModuleAppCompany[]> {
    return this.listModuleCompanyResponse().pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  createModuleCompanyResponse(body?: ModuleIdArray): Observable<HttpResponse<ModuleApp>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/module_company`,
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
        let _body: ModuleApp = null;
        _body = _resp.body as ModuleApp
        return _resp.clone({body: _body}) as HttpResponse<ModuleApp>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  createModuleCompany(body?: ModuleIdArray): Observable<ModuleApp> {
    return this.createModuleCompanyResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param moduleCompanyId - undefined
   */
  managerDeleteModuleCompanyResponse(moduleCompanyId: number): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "DELETE",
      this.rootUrl + `/manager/module_company/${moduleCompanyId}`,
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
   * @param moduleCompanyId - undefined
   */
  managerDeleteModuleCompany(moduleCompanyId: number): Observable<void> {
    return this.managerDeleteModuleCompanyResponse(moduleCompanyId).pipe(
      map(_r => _r.body)
    );
  }}

export module ManagerModuleCompanyService {
}
