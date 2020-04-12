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
import { MachineMsg } from '../models/machine-msg';
import { MachineParam } from '../models/machine-param';


@Injectable()
export class MachinePrepaidcardsService extends BaseService {
  constructor(
    config: ApiConfiguration,
    http: HttpClient
  ) {
    super(config, http);
  }

  /**
   * @param body - undefined
   */
  machineCreatePrepaidcardResponse(body?: PrepaidCardForm): Observable<HttpResponse<PrepaidCard>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/machine/prepaidcards/deposit`,
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
  machineCreatePrepaidcard(body?: PrepaidCardForm): Observable<PrepaidCard> {
    return this.machineCreatePrepaidcardResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  machineCreateMembershipResponse(body?: MachineParam): Observable<HttpResponse<MachineMsg>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/machine/membership/create`,
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
        let _body: MachineMsg = null;
        _body = _resp.body as MachineMsg
        return _resp.clone({body: _body}) as HttpResponse<MachineMsg>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  machineCreateMembership(body?: MachineParam): Observable<MachineMsg> {
    return this.machineCreateMembershipResponse(body).pipe(
      map(_r => _r.body)
    );
  }}

export module MachinePrepaidcardsService {
}
