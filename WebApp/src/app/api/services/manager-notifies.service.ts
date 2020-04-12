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

import { Notify } from '../models/notify';
import { NotifyReadForm } from '../models/notify-read-form';
import { NotifyInput } from '../models/notify-input';


@Injectable()
export class ManagerNotifiesService extends BaseService {
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
  managerListNotifiesResponse(params: ManagerNotifiesService.ManagerListNotifiesParams): Observable<HttpResponse<Notify[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    if (params.page != null) __params = __params.set("page", params.page.toString());
    if (params.limit != null) __params = __params.set("limit", params.limit.toString());
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/notifies`,
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
        let _body: Notify[] = null;
        _body = _resp.body as Notify[]
        return _resp.clone({body: _body}) as HttpResponse<Notify[]>;
      })
    );
  }

  /**
   * @param page - The number of items to skip before starting to collect the result set.
   * @param limit - The numbers of items to return.
   */
  managerListNotifies(params: ManagerNotifiesService.ManagerListNotifiesParams): Observable<Notify[]> {
    return this.managerListNotifiesResponse(params).pipe(
      map(_r => _r.body)
    );
  }
  /**
   */
  managerListNotifySharesResponse(): Observable<HttpResponse<Notify[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/notifies/share`,
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
        let _body: Notify[] = null;
        _body = _resp.body as Notify[]
        return _resp.clone({body: _body}) as HttpResponse<Notify[]>;
      })
    );
  }

  /**
   */
  managerListNotifyShares(): Observable<Notify[]> {
    return this.managerListNotifySharesResponse().pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param notifyId - undefined
   */
  managerGetNotifyByIdResponse(notifyId: number): Observable<HttpResponse<Notify>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/notifies/${notifyId}`,
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
        let _body: Notify = null;
        _body = _resp.body as Notify
        return _resp.clone({body: _body}) as HttpResponse<Notify>;
      })
    );
  }

  /**
   * @param notifyId - undefined
   */
  managerGetNotifyById(notifyId: number): Observable<Notify> {
    return this.managerGetNotifyByIdResponse(notifyId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param notifyId - undefined
   */
  managerDeleteNotifyResponse(notifyId: number): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "DELETE",
      this.rootUrl + `/manager/notifies/${notifyId}`,
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
   * @param notifyId - undefined
   */
  managerDeleteNotify(notifyId: number): Observable<void> {
    return this.managerDeleteNotifyResponse(notifyId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerUpdateReadedNotifyResponse(body?: NotifyReadForm): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/notifies/readed`,
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
   * @param body - undefined
   */
  managerUpdateReadedNotify(body?: NotifyReadForm): Observable<void> {
    return this.managerUpdateReadedNotifyResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerListNotifyByInputAndByTypeSearchResponse(body?: NotifyInput): Observable<HttpResponse<Notify[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/notifies/search`,
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
        let _body: Notify[] = null;
        _body = _resp.body as Notify[]
        return _resp.clone({body: _body}) as HttpResponse<Notify[]>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerListNotifyByInputAndByTypeSearch(body?: NotifyInput): Observable<Notify[]> {
    return this.managerListNotifyByInputAndByTypeSearchResponse(body).pipe(
      map(_r => _r.body)
    );
  }}

export module ManagerNotifiesService {
  export interface ManagerListNotifiesParams {
    page?: number;
    limit?: number;
  }
}
