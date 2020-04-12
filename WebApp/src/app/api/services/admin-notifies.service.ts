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

import { NotifyType } from '../models/notify-type';
import { NotifyTypeFrom } from '../models/notify-type-from';


@Injectable()
export class AdminNotifiesService extends BaseService {
  constructor(
    config: ApiConfiguration,
    http: HttpClient
  ) {
    super(config, http);
  }

  /**
   */
  listNotifyTypesResponse(): Observable<HttpResponse<NotifyType[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/admin/notifyTypes`,
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
        let _body: NotifyType[] = null;
        _body = _resp.body as NotifyType[]
        return _resp.clone({body: _body}) as HttpResponse<NotifyType[]>;
      })
    );
  }

  /**
   */
  listNotifyTypes(): Observable<NotifyType[]> {
    return this.listNotifyTypesResponse().pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  createNotifyTypeResponse(body?: NotifyTypeFrom): Observable<HttpResponse<NotifyType>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/admin/notifyTypes`,
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
        let _body: NotifyType = null;
        _body = _resp.body as NotifyType
        return _resp.clone({body: _body}) as HttpResponse<NotifyType>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  createNotifyType(body?: NotifyTypeFrom): Observable<NotifyType> {
    return this.createNotifyTypeResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  updateNotifyTypeResponse(body?: NotifyTypeFrom): Observable<HttpResponse<NotifyType>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "PATCH",
      this.rootUrl + `/admin/notifyTypes`,
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
        let _body: NotifyType = null;
        _body = _resp.body as NotifyType
        return _resp.clone({body: _body}) as HttpResponse<NotifyType>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  updateNotifyType(body?: NotifyTypeFrom): Observable<NotifyType> {
    return this.updateNotifyTypeResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param notifyTypeId - undefined
   */
  getNotifyTypeByIdResponse(notifyTypeId: number): Observable<HttpResponse<NotifyType>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/admin/notifyTypes/${notifyTypeId}`,
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
        let _body: NotifyType = null;
        _body = _resp.body as NotifyType
        return _resp.clone({body: _body}) as HttpResponse<NotifyType>;
      })
    );
  }

  /**
   * @param notifyTypeId - undefined
   */
  getNotifyTypeById(notifyTypeId: number): Observable<NotifyType> {
    return this.getNotifyTypeByIdResponse(notifyTypeId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param notifyTypeId - undefined
   */
  deleteNotifyTypeResponse(notifyTypeId: number): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "DELETE",
      this.rootUrl + `/admin/notifyTypes/${notifyTypeId}`,
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
   * @param notifyTypeId - undefined
   */
  deleteNotifyType(notifyTypeId: number): Observable<void> {
    return this.deleteNotifyTypeResponse(notifyTypeId).pipe(
      map(_r => _r.body)
    );
  }}

export module AdminNotifiesService {
}
