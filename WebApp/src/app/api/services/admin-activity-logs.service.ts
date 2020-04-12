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

import { ActivityLog } from '../models/activity-log';
import { ActivityLogFrom } from '../models/activity-log-from';
import { ActivityArr } from '../models/activity-arr';
import { ActivityLogSearchFrom } from '../models/activity-log-search-from';


@Injectable()
export class AdminActivityLogsService extends BaseService {
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
  listActivityLogResponse(params: AdminActivityLogsService.ListActivityLogParams): Observable<HttpResponse<ActivityLog[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    if (params.page != null) __params = __params.set("page", params.page.toString());
    if (params.limit != null) __params = __params.set("limit", params.limit.toString());
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/admin/activity_logs`,
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
        let _body: ActivityLog[] = null;
        _body = _resp.body as ActivityLog[]
        return _resp.clone({body: _body}) as HttpResponse<ActivityLog[]>;
      })
    );
  }

  /**
   * @param page - The number of items to skip before starting to collect the result set.
   * @param limit - The numbers of items to return.
   */
  listActivityLog(params: AdminActivityLogsService.ListActivityLogParams): Observable<ActivityLog[]> {
    return this.listActivityLogResponse(params).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  createActivityLogResponse(body?: ActivityLogFrom): Observable<HttpResponse<ActivityLog>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/admin/activity_logs`,
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
        let _body: ActivityLog = null;
        _body = _resp.body as ActivityLog
        return _resp.clone({body: _body}) as HttpResponse<ActivityLog>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  createActivityLog(body?: ActivityLogFrom): Observable<ActivityLog> {
    return this.createActivityLogResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param activityLogId - undefined
   */
  getActivityLogByIdResponse(activityLogId: number): Observable<HttpResponse<ActivityLog>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/admin/activity_logs/${activityLogId}`,
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
        let _body: ActivityLog = null;
        _body = _resp.body as ActivityLog
        return _resp.clone({body: _body}) as HttpResponse<ActivityLog>;
      })
    );
  }

  /**
   * @param activityLogId - undefined
   */
  getActivityLogById(activityLogId: number): Observable<ActivityLog> {
    return this.getActivityLogByIdResponse(activityLogId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param activityLogId - undefined
   */
  deleteActivityLogResponse(activityLogId: number): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "DELETE",
      this.rootUrl + `/admin/activity_logs/${activityLogId}`,
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
   * @param activityLogId - undefined
   */
  deleteActivityLog(activityLogId: number): Observable<void> {
    return this.deleteActivityLogResponse(activityLogId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  deleteActivityLogAllResponse(body?: ActivityArr): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/admin/activity_logs/deleteAll`,
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
  deleteActivityLogAll(body?: ActivityArr): Observable<void> {
    return this.deleteActivityLogAllResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  searchActivityLogResponse(body?: ActivityLogSearchFrom): Observable<HttpResponse<ActivityLog[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/admin/activity_logs/search`,
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
        let _body: ActivityLog[] = null;
        _body = _resp.body as ActivityLog[]
        return _resp.clone({body: _body}) as HttpResponse<ActivityLog[]>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  searchActivityLog(body?: ActivityLogSearchFrom): Observable<ActivityLog[]> {
    return this.searchActivityLogResponse(body).pipe(
      map(_r => _r.body)
    );
  }}

export module AdminActivityLogsService {
  export interface ListActivityLogParams {
    page?: number;
    limit?: number;
  }
}
