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

import { SettingGlobal } from '../models/setting-global';


@Injectable()
export class ManagerSettingGlobalService extends BaseService {
  constructor(
    config: ApiConfiguration,
    http: HttpClient
  ) {
    super(config, http);
  }

  /**
   */
  managerGetSettingGlobalResponse(): Observable<HttpResponse<SettingGlobal[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/settingGlobal`,
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
        let _body: SettingGlobal[] = null;
        _body = _resp.body as SettingGlobal[]
        return _resp.clone({body: _body}) as HttpResponse<SettingGlobal[]>;
      })
    );
  }

  /**
   */
  managerGetSettingGlobal(): Observable<SettingGlobal[]> {
    return this.managerGetSettingGlobalResponse().pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - Created setting global object
   */
  createSettingGlobalResponse(body?: SettingGlobal): Observable<HttpResponse<SettingGlobal>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/settingGlobal`,
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
        let _body: SettingGlobal = null;
        _body = _resp.body as SettingGlobal
        return _resp.clone({body: _body}) as HttpResponse<SettingGlobal>;
      })
    );
  }

  /**
   * @param body - Created setting global object
   */
  createSettingGlobal(body?: SettingGlobal): Observable<SettingGlobal> {
    return this.createSettingGlobalResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param value - undefined
   * @param key - undefined
   */
  deleteSettingGlobalResponse(params: ManagerSettingGlobalService.DeleteSettingGlobalParams): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    
    let req = new HttpRequest<any>(
      "DELETE",
      this.rootUrl + `/manager/settingGlobal/${params.key}/${params.value}`,
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
   * @param value - undefined
   * @param key - undefined
   */
  deleteSettingGlobal(params: ManagerSettingGlobalService.DeleteSettingGlobalParams): Observable<void> {
    return this.deleteSettingGlobalResponse(params).pipe(
      map(_r => _r.body)
    );
  }}

export module ManagerSettingGlobalService {
  export interface DeleteSettingGlobalParams {
    value: string;
    key: string;
  }
}
