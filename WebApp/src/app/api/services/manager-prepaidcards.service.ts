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

import { PrepaidCard } from '../models/prepaid-card';
import { PrepaidCardForm } from '../models/prepaid-card-form';


@Injectable()
export class ManagerPrepaidcardsService extends BaseService {
  constructor(
    config: ApiConfiguration,
    http: HttpClient
  ) {
    super(config, http);
  }

  /**
   */
  managerlistPrepaidcardsResponse(): Observable<HttpResponse<PrepaidCard[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/prepaidcards`,
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
        let _body: PrepaidCard[] = null;
        _body = _resp.body as PrepaidCard[]
        return _resp.clone({body: _body}) as HttpResponse<PrepaidCard[]>;
      })
    );
  }

  /**
   */
  managerlistPrepaidcards(): Observable<PrepaidCard[]> {
    return this.managerlistPrepaidcardsResponse().pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerCreatePrepaidcardResponse(body?: PrepaidCardForm): Observable<HttpResponse<PrepaidCard>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/prepaidcards`,
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
        let _body: PrepaidCard = null;
        _body = _resp.body as PrepaidCard
        return _resp.clone({body: _body}) as HttpResponse<PrepaidCard>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerCreatePrepaidcard(body?: PrepaidCardForm): Observable<PrepaidCard> {
    return this.managerCreatePrepaidcardResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerUpdatePrepaidcardResponse(body?: PrepaidCardForm): Observable<HttpResponse<PrepaidCard>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "PATCH",
      this.rootUrl + `/manager/prepaidcards`,
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
        let _body: PrepaidCard = null;
        _body = _resp.body as PrepaidCard
        return _resp.clone({body: _body}) as HttpResponse<PrepaidCard>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerUpdatePrepaidcard(body?: PrepaidCardForm): Observable<PrepaidCard> {
    return this.managerUpdatePrepaidcardResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param prepaidcardId - undefined
   */
  managerGetPrepaidcardsByIdResponse(prepaidcardId: number): Observable<HttpResponse<PrepaidCard>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/prepaidcards/${prepaidcardId}`,
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
        let _body: PrepaidCard = null;
        _body = _resp.body as PrepaidCard
        return _resp.clone({body: _body}) as HttpResponse<PrepaidCard>;
      })
    );
  }

  /**
   * @param prepaidcardId - undefined
   */
  managerGetPrepaidcardsById(prepaidcardId: number): Observable<PrepaidCard> {
    return this.managerGetPrepaidcardsByIdResponse(prepaidcardId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param prepaidcardId - undefined
   */
  managerDeletePrepaidcardResponse(prepaidcardId: number): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "DELETE",
      this.rootUrl + `/manager/prepaidcards/${prepaidcardId}`,
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
   * @param prepaidcardId - undefined
   */
  managerDeletePrepaidcard(prepaidcardId: number): Observable<void> {
    return this.managerDeletePrepaidcardResponse(prepaidcardId).pipe(
      map(_r => _r.body)
    );
  }}

export module ManagerPrepaidcardsService {
}
