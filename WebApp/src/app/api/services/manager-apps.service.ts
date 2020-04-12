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

import { Application } from '../models/application';


@Injectable()
export class ManagerAppsService extends BaseService {
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
  managerAppsListResponse(params: ManagerAppsService.ManagerAppsListParams): Observable<HttpResponse<Application[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    if (params.page != null) __params = __params.set("page", params.page.toString());
    if (params.limit != null) __params = __params.set("limit", params.limit.toString());
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/apps`,
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
        let _body: Application[] = null;
        _body = _resp.body as Application[]
        return _resp.clone({body: _body}) as HttpResponse<Application[]>;
      })
    );
  }

  /**
   * @param page - The number of items to skip before starting to collect the result set.
   * @param limit - The numbers of items to return.
   */
  managerAppsList(params: ManagerAppsService.ManagerAppsListParams): Observable<Application[]> {
    return this.managerAppsListResponse(params).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerAppsCreateResponse(body?: Application): Observable<HttpResponse<Application>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/apps`,
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
        let _body: Application = null;
        _body = _resp.body as Application
        return _resp.clone({body: _body}) as HttpResponse<Application>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerAppsCreate(body?: Application): Observable<Application> {
    return this.managerAppsCreateResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerAppsUpdateResponse(body?: Application): Observable<HttpResponse<Application>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "PATCH",
      this.rootUrl + `/manager/apps`,
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
        let _body: Application = null;
        _body = _resp.body as Application
        return _resp.clone({body: _body}) as HttpResponse<Application>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerAppsUpdate(body?: Application): Observable<Application> {
    return this.managerAppsUpdateResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param appId - undefined
   */
  managerAppsGetByIdResponse(appId: number): Observable<HttpResponse<Application>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/apps/${appId}`,
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
        let _body: Application = null;
        _body = _resp.body as Application
        return _resp.clone({body: _body}) as HttpResponse<Application>;
      })
    );
  }

  /**
   * @param appId - undefined
   */
  managerAppsGetById(appId: number): Observable<Application> {
    return this.managerAppsGetByIdResponse(appId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param appId - undefined
   */
  managerAppsDeleteResponse(appId: number): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "DELETE",
      this.rootUrl + `/manager/apps/${appId}`,
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
   * @param appId - undefined
   */
  managerAppsDelete(appId: number): Observable<void> {
    return this.managerAppsDeleteResponse(appId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param appId - undefined
   */
  managerAppsChangeApiKeyByIdResponse(appId: number): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/apps/${appId}/changeApiKey`,
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
   * @param appId - undefined
   */
  managerAppsChangeApiKeyById(appId: number): Observable<void> {
    return this.managerAppsChangeApiKeyByIdResponse(appId).pipe(
      map(_r => _r.body)
    );
  }}

export module ManagerAppsService {
  export interface ManagerAppsListParams {
    page?: number;
    limit?: number;
  }
}
