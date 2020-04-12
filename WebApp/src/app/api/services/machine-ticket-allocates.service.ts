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

import { TicketAllocate } from '../models/ticket-allocate';


@Injectable()
export class MachineTicketAllocatesService extends BaseService {
  constructor(
    config: ApiConfiguration,
    http: HttpClient
  ) {
    super(config, http);
  }

  /**
   * @param body - undefined
   */
  machineTicketAllocatesResponse(body?: number[]): Observable<HttpResponse<TicketAllocate[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/machine/ticketAllocates/update`,
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
        let _body: TicketAllocate[] = null;
        _body = _resp.body as TicketAllocate[]
        return _resp.clone({body: _body}) as HttpResponse<TicketAllocate[]>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  machineTicketAllocates(body?: number[]): Observable<TicketAllocate[]> {
    return this.machineTicketAllocatesResponse(body).pipe(
      map(_r => _r.body)
    );
  }}

export module MachineTicketAllocatesService {
}
