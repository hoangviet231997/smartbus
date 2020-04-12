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


@Injectable()
export class MobilePrepaidcardsService extends BaseService {
  constructor(
    config: ApiConfiguration,
    http: HttpClient
  ) {
    super(config, http);
  }

  /**
   * @param barcode - undefined
   */
  mobileGetPrepaidcardByBarcodeResponse(barcode: string): Observable<HttpResponse<PrepaidCard>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/mobile/prepaidcards/barcode/${barcode}`,
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
   * @param barcode - undefined
   */
  mobileGetPrepaidcardByBarcode(barcode: string): Observable<PrepaidCard> {
    return this.mobileGetPrepaidcardByBarcodeResponse(barcode).pipe(
      map(_r => _r.body)
    );
  }}

export module MobilePrepaidcardsService {
}
