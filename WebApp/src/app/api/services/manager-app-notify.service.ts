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

import { AppNotify } from '../models/app-notify';
import { AppNotifyForm } from '../models/app-notify-form';
import { AppNotifyInputForm } from '../models/app-notify-input-form';


@Injectable()
export class ManagerAppNotifyService extends BaseService {
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
  managerListAppNotifyResponse(params: ManagerAppNotifyService.ManagerListAppNotifyParams): Observable<HttpResponse<AppNotify[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    if (params.page != null) __params = __params.set("page", params.page.toString());
    if (params.limit != null) __params = __params.set("limit", params.limit.toString());
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/appNotify`,
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
        let _body: AppNotify[] = null;
        _body = _resp.body as AppNotify[]
        return _resp.clone({body: _body}) as HttpResponse<AppNotify[]>;
      })
    );
  }

  /**
   * @param page - The number of items to skip before starting to collect the result set.
   * @param limit - The numbers of items to return.
   */
  managerListAppNotify(params: ManagerAppNotifyService.ManagerListAppNotifyParams): Observable<AppNotify[]> {
    return this.managerListAppNotifyResponse(params).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerCreateAppNotifyResponse(body?: AppNotifyForm): Observable<HttpResponse<AppNotify>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/appNotify`,
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
        let _body: AppNotify = null;
        _body = _resp.body as AppNotify
        return _resp.clone({body: _body}) as HttpResponse<AppNotify>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerCreateAppNotify(body?: AppNotifyForm): Observable<AppNotify> {
    return this.managerCreateAppNotifyResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerUpdateAppNotifyResponse(body?: AppNotifyForm): Observable<HttpResponse<AppNotify>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "PATCH",
      this.rootUrl + `/manager/appNotify`,
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
        let _body: AppNotify = null;
        _body = _resp.body as AppNotify
        return _resp.clone({body: _body}) as HttpResponse<AppNotify>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerUpdateAppNotify(body?: AppNotifyForm): Observable<AppNotify> {
    return this.managerUpdateAppNotifyResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param appNotifyID - undefined
   */
  managerGetAppNotifyByIdResponse(appNotifyID: number): Observable<HttpResponse<AppNotify>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/appNotify/${appNotifyID}`,
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
        let _body: AppNotify = null;
        _body = _resp.body as AppNotify
        return _resp.clone({body: _body}) as HttpResponse<AppNotify>;
      })
    );
  }

  /**
   * @param appNotifyID - undefined
   */
  managerGetAppNotifyById(appNotifyID: number): Observable<AppNotify> {
    return this.managerGetAppNotifyByIdResponse(appNotifyID).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param appNotifyID - undefined
   */
  managerDeleteAppNotifyResponse(appNotifyID: number): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "DELETE",
      this.rootUrl + `/manager/appNotify/${appNotifyID}`,
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
   * @param appNotifyID - undefined
   */
  managerDeleteAppNotify(appNotifyID: number): Observable<void> {
    return this.managerDeleteAppNotifyResponse(appNotifyID).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerSearchAppNotifyByInputResponse(body?: AppNotifyInputForm): Observable<HttpResponse<AppNotify>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/appNotify/search`,
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
        let _body: AppNotify = null;
        _body = _resp.body as AppNotify
        return _resp.clone({body: _body}) as HttpResponse<AppNotify>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerSearchAppNotifyByInput(body?: AppNotifyInputForm): Observable<AppNotify> {
    return this.managerSearchAppNotifyByInputResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   */
  managerGetAppNotifyForAppResponse(): Observable<HttpResponse<AppNotify[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/appNotify/app`,
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
        let _body: AppNotify[] = null;
        _body = _resp.body as AppNotify[]
        return _resp.clone({body: _body}) as HttpResponse<AppNotify[]>;
      })
    );
  }

  /**
   */
  managerGetAppNotifyForApp(): Observable<AppNotify[]> {
    return this.managerGetAppNotifyForAppResponse().pipe(
      map(_r => _r.body)
    );
  }}

export module ManagerAppNotifyService {
  export interface ManagerListAppNotifyParams {
    page?: number;
    limit?: number;
  }
}
