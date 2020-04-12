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



@Injectable()
export class MachineGetService extends BaseService {
  constructor(
    config: ApiConfiguration,
    http: HttpClient
  ) {
    super(config, http);
  }

  /**
   * @param from - undefined
   */
  machineGetCountTicketGoodsByDeviceResponse(from?: number): Observable<HttpResponse<number>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    if (from != null) __params = __params.set("from", from.toString());
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/machine/get/transaction/check/goods`,
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
        let _body: number = null;
        _body = parseFloat(_resp.body as string)
        return _resp.clone({body: _body}) as HttpResponse<number>;
      })
    );
  }

  /**
   * @param from - undefined
   */
  machineGetCountTicketGoodsByDevice(from?: number): Observable<number> {
    return this.machineGetCountTicketGoodsByDeviceResponse(from).pipe(
      map(_r => _r.body)
    );
  }}

export module MachineGetService {
}
