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

import { ModuleApp } from '../models/module-app';
import { ModuleAppForm } from '../models/module-app-form';


@Injectable()
export class AdminModuleAppsService extends BaseService {
  constructor(
    config: ApiConfiguration,
    http: HttpClient
  ) {
    super(config, http);
  }

  /**
   */
  listModuleAppResponse(): Observable<HttpResponse<ModuleApp[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/admin/module_apps`,
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
        let _body: ModuleApp[] = null;
        _body = _resp.body as ModuleApp[]
        return _resp.clone({body: _body}) as HttpResponse<ModuleApp[]>;
      })
    );
  }

  /**
   */
  listModuleApp(): Observable<ModuleApp[]> {
    return this.listModuleAppResponse().pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  createModuleAppResponse(body?: ModuleAppForm): Observable<HttpResponse<ModuleApp>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/admin/module_apps`,
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
  createModuleApp(body?: ModuleAppForm): Observable<ModuleApp> {
    return this.createModuleAppResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  updateModuleAppResponse(body?: ModuleAppForm): Observable<HttpResponse<ModuleApp>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "PATCH",
      this.rootUrl + `/admin/module_apps`,
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
  updateModuleApp(body?: ModuleAppForm): Observable<ModuleApp> {
    return this.updateModuleAppResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param moduleId - undefined
   */
  getModuleAppByIdResponse(moduleId: number): Observable<HttpResponse<ModuleApp>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/admin/module_apps/${moduleId}`,
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
   * @param moduleId - undefined
   */
  getModuleAppById(moduleId: number): Observable<ModuleApp> {
    return this.getModuleAppByIdResponse(moduleId).pipe(
      map(_r => _r.body)
    );
  }}

export module AdminModuleAppsService {
}
