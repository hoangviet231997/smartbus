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

import { Category } from '../models/category';
import { CategoryFrom } from '../models/category-from';
import { CategoryInput } from '../models/category-input';


@Injectable()
export class AdminCategoriesService extends BaseService {
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
  listCategoryResponse(params: AdminCategoriesService.ListCategoryParams): Observable<HttpResponse<Category[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    if (params.page != null) __params = __params.set("page", params.page.toString());
    if (params.limit != null) __params = __params.set("limit", params.limit.toString());
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/admin/categories`,
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
        let _body: Category[] = null;
        _body = _resp.body as Category[]
        return _resp.clone({body: _body}) as HttpResponse<Category[]>;
      })
    );
  }

  /**
   * @param page - The number of items to skip before starting to collect the result set.
   * @param limit - The numbers of items to return.
   */
  listCategory(params: AdminCategoriesService.ListCategoryParams): Observable<Category[]> {
    return this.listCategoryResponse(params).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  createCategoryResponse(body?: CategoryFrom): Observable<HttpResponse<Category>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/admin/categories`,
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
        let _body: Category = null;
        _body = _resp.body as Category
        return _resp.clone({body: _body}) as HttpResponse<Category>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  createCategory(body?: CategoryFrom): Observable<Category> {
    return this.createCategoryResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  updateCategoryResponse(body?: CategoryFrom): Observable<HttpResponse<Category>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "PATCH",
      this.rootUrl + `/admin/categories`,
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
        let _body: Category = null;
        _body = _resp.body as Category
        return _resp.clone({body: _body}) as HttpResponse<Category>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  updateCategory(body?: CategoryFrom): Observable<Category> {
    return this.updateCategoryResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param categoryId - undefined
   */
  getCategoryByIdResponse(categoryId: number): Observable<HttpResponse<Category>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/admin/categories/${categoryId}`,
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
        let _body: Category = null;
        _body = _resp.body as Category
        return _resp.clone({body: _body}) as HttpResponse<Category>;
      })
    );
  }

  /**
   * @param categoryId - undefined
   */
  getCategoryById(categoryId: number): Observable<Category> {
    return this.getCategoryByIdResponse(categoryId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param categoryId - undefined
   */
  deleteCategoryResponse(categoryId: number): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "DELETE",
      this.rootUrl + `/admin/categories/${categoryId}`,
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
   * @param categoryId - undefined
   */
  deleteCategory(categoryId: number): Observable<void> {
    return this.deleteCategoryResponse(categoryId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerListCategoryByInputAndByTypeSearchResponse(body?: CategoryInput): Observable<HttpResponse<Category[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/admin/categories/search`,
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
        let _body: Category[] = null;
        _body = _resp.body as Category[]
        return _resp.clone({body: _body}) as HttpResponse<Category[]>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerListCategoryByInputAndByTypeSearch(body?: CategoryInput): Observable<Category[]> {
    return this.managerListCategoryByInputAndByTypeSearchResponse(body).pipe(
      map(_r => _r.body)
    );
  }}

export module AdminCategoriesService {
  export interface ListCategoryParams {
    page?: number;
    limit?: number;
  }
}
