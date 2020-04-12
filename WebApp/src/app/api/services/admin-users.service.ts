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

import { User } from '../models/user';
import { UserCreate } from '../models/user-create';
import { UserUpdate } from '../models/user-update';
import { ChangePasswordUser } from '../models/change-password-user';


@Injectable()
export class AdminUsersService extends BaseService {
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
  listUsersResponse(params: AdminUsersService.ListUsersParams): Observable<HttpResponse<User[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    if (params.page != null) __params = __params.set("page", params.page.toString());
    if (params.limit != null) __params = __params.set("limit", params.limit.toString());
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/admin/users`,
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
        let _body: User[] = null;
        _body = _resp.body as User[]
        return _resp.clone({body: _body}) as HttpResponse<User[]>;
      })
    );
  }

  /**
   * @param page - The number of items to skip before starting to collect the result set.
   * @param limit - The numbers of items to return.
   */
  listUsers(params: AdminUsersService.ListUsersParams): Observable<User[]> {
    return this.listUsersResponse(params).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - Created User object
   */
  createUserResponse(body?: UserCreate): Observable<HttpResponse<User>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/admin/users`,
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
        let _body: User = null;
        _body = _resp.body as User
        return _resp.clone({body: _body}) as HttpResponse<User>;
      })
    );
  }

  /**
   * @param body - Created User object
   */
  createUser(body?: UserCreate): Observable<User> {
    return this.createUserResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - update
   */
  updateUserResponse(body?: UserUpdate): Observable<HttpResponse<User>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "PATCH",
      this.rootUrl + `/admin/users`,
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
        let _body: User = null;
        _body = _resp.body as User
        return _resp.clone({body: _body}) as HttpResponse<User>;
      })
    );
  }

  /**
   * @param body - update
   */
  updateUser(body?: UserUpdate): Observable<User> {
    return this.updateUserResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param userId - undefined
   */
  getUserResponse(userId: number): Observable<HttpResponse<User>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/admin/users/${userId}`,
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
        let _body: User = null;
        _body = _resp.body as User
        return _resp.clone({body: _body}) as HttpResponse<User>;
      })
    );
  }

  /**
   * @param userId - undefined
   */
  getUser(userId: number): Observable<User> {
    return this.getUserResponse(userId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param userId - undefined
   */
  deleteUserResponse(userId: number): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "DELETE",
      this.rootUrl + `/admin/users/${userId}`,
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
   * @param userId - undefined
   */
  deleteUser(userId: number): Observable<void> {
    return this.deleteUserResponse(userId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param userId - ID of user that needs to be user
   * @param body - Change Password
   */
  changePasswordOfUserResponse(params: AdminUsersService.ChangePasswordOfUserParams): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    __body = params.body;
    let req = new HttpRequest<any>(
      "PATCH",
      this.rootUrl + `/admin/users/${params.userId}/changepassword`,
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
   * @param userId - ID of user that needs to be user
   * @param body - Change Password
   */
  changePasswordOfUser(params: AdminUsersService.ChangePasswordOfUserParams): Observable<void> {
    return this.changePasswordOfUserResponse(params).pipe(
      map(_r => _r.body)
    );
  }
  /**
   */
  listAllUserResponse(): Observable<HttpResponse<User[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/admin/users/get_all`,
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
        let _body: User[] = null;
        _body = _resp.body as User[]
        return _resp.clone({body: _body}) as HttpResponse<User[]>;
      })
    );
  }

  /**
   */
  listAllUser(): Observable<User[]> {
    return this.listAllUserResponse().pipe(
      map(_r => _r.body)
    );
  }}

export module AdminUsersService {
  export interface ListUsersParams {
    page?: number;
    limit?: number;
  }
  export interface ChangePasswordOfUserParams {
    userId: number;
    body: ChangePasswordUser;
  }
}
