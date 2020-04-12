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

import { SubscriptionType } from '../models/subscription-type';
import { SubscriptionTypeForm } from '../models/subscription-type-form';


@Injectable()
export class ManagerSubscriptionTypesService extends BaseService {
  constructor(
    config: ApiConfiguration,
    http: HttpClient
  ) {
    super(config, http);
  }

  /**
   */
  managerlistSubscriptionTypesResponse(): Observable<HttpResponse<SubscriptionType[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/subscriptionTypes`,
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
        let _body: SubscriptionType[] = null;
        _body = _resp.body as SubscriptionType[]
        return _resp.clone({body: _body}) as HttpResponse<SubscriptionType[]>;
      })
    );
  }

  /**
   */
  managerlistSubscriptionTypes(): Observable<SubscriptionType[]> {
    return this.managerlistSubscriptionTypesResponse().pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  manmagerCreateSubscriptionTypeResponse(body?: SubscriptionTypeForm): Observable<HttpResponse<SubscriptionType>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/subscriptionTypes`,
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
        let _body: SubscriptionType = null;
        _body = _resp.body as SubscriptionType
        return _resp.clone({body: _body}) as HttpResponse<SubscriptionType>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  manmagerCreateSubscriptionType(body?: SubscriptionTypeForm): Observable<SubscriptionType> {
    return this.manmagerCreateSubscriptionTypeResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerUpdateSubscriptionTypeResponse(body?: SubscriptionTypeForm): Observable<HttpResponse<SubscriptionType>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "PATCH",
      this.rootUrl + `/manager/subscriptionTypes`,
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
        let _body: SubscriptionType = null;
        _body = _resp.body as SubscriptionType
        return _resp.clone({body: _body}) as HttpResponse<SubscriptionType>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerUpdateSubscriptionType(body?: SubscriptionTypeForm): Observable<SubscriptionType> {
    return this.managerUpdateSubscriptionTypeResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param subscriptionTypeId - undefined
   */
  managerGetSubscriptionTypeByIdResponse(subscriptionTypeId: number): Observable<HttpResponse<SubscriptionType>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/subscriptionTypes/${subscriptionTypeId}`,
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
        let _body: SubscriptionType = null;
        _body = _resp.body as SubscriptionType
        return _resp.clone({body: _body}) as HttpResponse<SubscriptionType>;
      })
    );
  }

  /**
   * @param subscriptionTypeId - undefined
   */
  managerGetSubscriptionTypeById(subscriptionTypeId: number): Observable<SubscriptionType> {
    return this.managerGetSubscriptionTypeByIdResponse(subscriptionTypeId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param subscriptionTypeId - undefined
   */
  managerDeleteSubscriptionTypeResponse(subscriptionTypeId: number): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "DELETE",
      this.rootUrl + `/manager/subscriptionTypes/${subscriptionTypeId}`,
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
   * @param subscriptionTypeId - undefined
   */
  managerDeleteSubscriptionType(subscriptionTypeId: number): Observable<void> {
    return this.managerDeleteSubscriptionTypeResponse(subscriptionTypeId).pipe(
      map(_r => _r.body)
    );
  }}

export module ManagerSubscriptionTypesService {
}
