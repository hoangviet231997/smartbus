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

import { DevModel } from '../models/dev-model';
import { DevModelForm } from '../models/dev-model-form';
import { Firmware } from '../models/firmware';
import { FirmwareForm } from '../models/firmware-form';
import { Device } from '../models/device';
import { DeviceForm } from '../models/device-form';
import { DeviceInput } from '../models/device-input';


@Injectable()
export class AdminDevicesService extends BaseService {
  constructor(
    config: ApiConfiguration,
    http: HttpClient
  ) {
    super(config, http);
  }

  /**
   */
  listDevModelsResponse(): Observable<HttpResponse<DevModel[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/admin/devices/models`,
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
        let _body: DevModel[] = null;
        _body = _resp.body as DevModel[]
        return _resp.clone({body: _body}) as HttpResponse<DevModel[]>;
      })
    );
  }

  /**
   */
  listDevModels(): Observable<DevModel[]> {
    return this.listDevModelsResponse().pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  createDevModelResponse(body?: DevModelForm): Observable<HttpResponse<DevModel>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/admin/devices/models`,
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
        let _body: DevModel = null;
        _body = _resp.body as DevModel
        return _resp.clone({body: _body}) as HttpResponse<DevModel>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  createDevModel(body?: DevModelForm): Observable<DevModel> {
    return this.createDevModelResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  updateDevModelResponse(body?: DevModelForm): Observable<HttpResponse<DevModel>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "PATCH",
      this.rootUrl + `/admin/devices/models`,
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
        let _body: DevModel = null;
        _body = _resp.body as DevModel
        return _resp.clone({body: _body}) as HttpResponse<DevModel>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  updateDevModel(body?: DevModelForm): Observable<DevModel> {
    return this.updateDevModelResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param modelId - undefined
   */
  getDevModelByIdResponse(modelId: number): Observable<HttpResponse<DevModel>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/admin/devices/models/${modelId}`,
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
        let _body: DevModel = null;
        _body = _resp.body as DevModel
        return _resp.clone({body: _body}) as HttpResponse<DevModel>;
      })
    );
  }

  /**
   * @param modelId - undefined
   */
  getDevModelById(modelId: number): Observable<DevModel> {
    return this.getDevModelByIdResponse(modelId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param modelId - undefined
   */
  deleteDevModelResponse(modelId: number): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "DELETE",
      this.rootUrl + `/admin/devices/models/${modelId}`,
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
   * @param modelId - undefined
   */
  deleteDevModel(modelId: number): Observable<void> {
    return this.deleteDevModelResponse(modelId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param page - The number of items to skip before starting to collect the result set.
   * @param limit - The numbers of items to return.
   */
  listFirmwareVersionsResponse(params: AdminDevicesService.ListFirmwareVersionsParams): Observable<HttpResponse<Firmware[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    if (params.page != null) __params = __params.set("page", params.page.toString());
    if (params.limit != null) __params = __params.set("limit", params.limit.toString());
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/admin/devices/firmwares`,
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
        let _body: Firmware[] = null;
        _body = _resp.body as Firmware[]
        return _resp.clone({body: _body}) as HttpResponse<Firmware[]>;
      })
    );
  }

  /**
   * @param page - The number of items to skip before starting to collect the result set.
   * @param limit - The numbers of items to return.
   */
  listFirmwareVersions(params: AdminDevicesService.ListFirmwareVersionsParams): Observable<Firmware[]> {
    return this.listFirmwareVersionsResponse(params).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  createFirmWareDeviceVersionResponse(body?: FirmwareForm): Observable<HttpResponse<Firmware>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/admin/devices/firmwares`,
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
        let _body: Firmware = null;
        _body = _resp.body as Firmware
        return _resp.clone({body: _body}) as HttpResponse<Firmware>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  createFirmWareDeviceVersion(body?: FirmwareForm): Observable<Firmware> {
    return this.createFirmWareDeviceVersionResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param firmwareId - undefined
   */
  getFirmwaresByIdResponse(firmwareId: number): Observable<HttpResponse<Firmware>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/admin/devices/firmwares/${firmwareId}`,
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
        let _body: Firmware = null;
        _body = _resp.body as Firmware
        return _resp.clone({body: _body}) as HttpResponse<Firmware>;
      })
    );
  }

  /**
   * @param firmwareId - undefined
   */
  getFirmwaresById(firmwareId: number): Observable<Firmware> {
    return this.getFirmwaresByIdResponse(firmwareId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param firmwareId - undefined
   */
  deleteFirmWareDeviceVersionResponse(firmwareId: number): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "DELETE",
      this.rootUrl + `/admin/devices/firmwares/${firmwareId}`,
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
   * @param firmwareId - undefined
   */
  deleteFirmWareDeviceVersion(firmwareId: number): Observable<void> {
    return this.deleteFirmWareDeviceVersionResponse(firmwareId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param filename - undefined
   */
  downloadFirmwareVersionResponse(filename: string): Observable<HttpResponse<string>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/admin/devices/firmwares/download/${filename}`,
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
        let _body: string = null;
        _body = _resp.body as string
        return _resp.clone({body: _body}) as HttpResponse<string>;
      })
    );
  }

  /**
   * @param filename - undefined
   */
  downloadFirmwareVersion(filename: string): Observable<string> {
    return this.downloadFirmwareVersionResponse(filename).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param modelId - undefined
   */
  listFirmwaresResponse(modelId: number): Observable<HttpResponse<Firmware[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/admin/devices/models/${modelId}/firmwares`,
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
        let _body: Firmware[] = null;
        _body = _resp.body as Firmware[]
        return _resp.clone({body: _body}) as HttpResponse<Firmware[]>;
      })
    );
  }

  /**
   * @param modelId - undefined
   */
  listFirmwares(modelId: number): Observable<Firmware[]> {
    return this.listFirmwaresResponse(modelId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param modelId - undefined
   * @param body - undefined
   */
  createFirmwareResponse(params: AdminDevicesService.CreateFirmwareParams): Observable<HttpResponse<Firmware>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    __body = params.body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/admin/devices/models/${params.modelId}/firmwares`,
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
        let _body: Firmware = null;
        _body = _resp.body as Firmware
        return _resp.clone({body: _body}) as HttpResponse<Firmware>;
      })
    );
  }

  /**
   * @param modelId - undefined
   * @param body - undefined
   */
  createFirmware(params: AdminDevicesService.CreateFirmwareParams): Observable<Firmware> {
    return this.createFirmwareResponse(params).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param modelId - undefined
   * @param body - undefined
   */
  updateFirmwareResponse(params: AdminDevicesService.UpdateFirmwareParams): Observable<HttpResponse<Firmware>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    __body = params.body;
    let req = new HttpRequest<any>(
      "PATCH",
      this.rootUrl + `/admin/devices/models/${params.modelId}/firmwares`,
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
        let _body: Firmware = null;
        _body = _resp.body as Firmware
        return _resp.clone({body: _body}) as HttpResponse<Firmware>;
      })
    );
  }

  /**
   * @param modelId - undefined
   * @param body - undefined
   */
  updateFirmware(params: AdminDevicesService.UpdateFirmwareParams): Observable<Firmware> {
    return this.updateFirmwareResponse(params).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param modelId - undefined
   * @param firmwareId - undefined
   */
  getFirmwareByIdAndModelIdResponse(params: AdminDevicesService.GetFirmwareByIdAndModelIdParams): Observable<HttpResponse<Firmware>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/admin/devices/models/${params.modelId}/firmwares/${params.firmwareId}`,
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
        let _body: Firmware = null;
        _body = _resp.body as Firmware
        return _resp.clone({body: _body}) as HttpResponse<Firmware>;
      })
    );
  }

  /**
   * @param modelId - undefined
   * @param firmwareId - undefined
   */
  getFirmwareByIdAndModelId(params: AdminDevicesService.GetFirmwareByIdAndModelIdParams): Observable<Firmware> {
    return this.getFirmwareByIdAndModelIdResponse(params).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param modelId - undefined
   * @param firmwareId - undefined
   */
  deleteFirmwareResponse(params: AdminDevicesService.DeleteFirmwareParams): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    
    let req = new HttpRequest<any>(
      "DELETE",
      this.rootUrl + `/admin/devices/models/${params.modelId}/firmwares/${params.firmwareId}`,
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
   * @param modelId - undefined
   * @param firmwareId - undefined
   */
  deleteFirmware(params: AdminDevicesService.DeleteFirmwareParams): Observable<void> {
    return this.deleteFirmwareResponse(params).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param page - The number of items to skip before starting to collect the result set.
   * @param limit - The numbers of items to return.
   */
  listDevicesResponse(params: AdminDevicesService.ListDevicesParams): Observable<HttpResponse<Device[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    if (params.page != null) __params = __params.set("page", params.page.toString());
    if (params.limit != null) __params = __params.set("limit", params.limit.toString());
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/admin/devices`,
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
        let _body: Device[] = null;
        _body = _resp.body as Device[]
        return _resp.clone({body: _body}) as HttpResponse<Device[]>;
      })
    );
  }

  /**
   * @param page - The number of items to skip before starting to collect the result set.
   * @param limit - The numbers of items to return.
   */
  listDevices(params: AdminDevicesService.ListDevicesParams): Observable<Device[]> {
    return this.listDevicesResponse(params).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  createDeviceResponse(body?: DeviceForm): Observable<HttpResponse<Device>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/admin/devices`,
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
        let _body: Device = null;
        _body = _resp.body as Device
        return _resp.clone({body: _body}) as HttpResponse<Device>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  createDevice(body?: DeviceForm): Observable<Device> {
    return this.createDeviceResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  updateDeviceResponse(body?: DeviceForm): Observable<HttpResponse<Device>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "PATCH",
      this.rootUrl + `/admin/devices`,
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
        let _body: Device = null;
        _body = _resp.body as Device
        return _resp.clone({body: _body}) as HttpResponse<Device>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  updateDevice(body?: DeviceForm): Observable<Device> {
    return this.updateDeviceResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param deviceId - undefined
   */
  getDeviceByIdResponse(deviceId: number): Observable<HttpResponse<Device>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/admin/devices/${deviceId}`,
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
        let _body: Device = null;
        _body = _resp.body as Device
        return _resp.clone({body: _body}) as HttpResponse<Device>;
      })
    );
  }

  /**
   * @param deviceId - undefined
   */
  getDeviceById(deviceId: number): Observable<Device> {
    return this.getDeviceByIdResponse(deviceId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param deviceId - undefined
   */
  deleteDeviceResponse(deviceId: number): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "DELETE",
      this.rootUrl + `/admin/devices/${deviceId}`,
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
   * @param deviceId - undefined
   */
  deleteDevice(deviceId: number): Observable<void> {
    return this.deleteDeviceResponse(deviceId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param txtIdentity - undefined
   */
  getDeviceByIdentitySearchResponse(txtIdentity: string): Observable<HttpResponse<Device[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/admin/devices/search/${txtIdentity}`,
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
        let _body: Device[] = null;
        _body = _resp.body as Device[]
        return _resp.clone({body: _body}) as HttpResponse<Device[]>;
      })
    );
  }

  /**
   * @param txtIdentity - undefined
   */
  getDeviceByIdentitySearch(txtIdentity: string): Observable<Device[]> {
    return this.getDeviceByIdentitySearchResponse(txtIdentity).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param deviceId - undefined
   * @param companyId - undefined
   */
  assignCompanyToDeviceResponse(params: AdminDevicesService.AssignCompanyToDeviceParams): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/admin/devices/${params.deviceId}/company/${params.companyId}`,
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
   * @param deviceId - undefined
   * @param companyId - undefined
   */
  assignCompanyToDevice(params: AdminDevicesService.AssignCompanyToDeviceParams): Observable<void> {
    return this.assignCompanyToDeviceResponse(params).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param deviceId - undefined
   * @param companyId - undefined
   */
  deleteAssignCompanyToDeviceResponse(params: AdminDevicesService.DeleteAssignCompanyToDeviceParams): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    
    let req = new HttpRequest<any>(
      "DELETE",
      this.rootUrl + `/admin/devices/${params.deviceId}/company/${params.companyId}`,
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
   * @param deviceId - undefined
   * @param companyId - undefined
   */
  deleteAssignCompanyToDevice(params: AdminDevicesService.DeleteAssignCompanyToDeviceParams): Observable<void> {
    return this.deleteAssignCompanyToDeviceResponse(params).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  searchFirmwareByInputAndByTypeSearchResponse(body?: DeviceInput): Observable<HttpResponse<DevModel[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/admin/devices/search`,
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
        let _body: DevModel[] = null;
        _body = _resp.body as DevModel[]
        return _resp.clone({body: _body}) as HttpResponse<DevModel[]>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  searchFirmwareByInputAndByTypeSearch(body?: DeviceInput): Observable<DevModel[]> {
    return this.searchFirmwareByInputAndByTypeSearchResponse(body).pipe(
      map(_r => _r.body)
    );
  }}

export module AdminDevicesService {
  export interface ListFirmwareVersionsParams {
    page?: number;
    limit?: number;
  }
  export interface CreateFirmwareParams {
    modelId: number;
    body?: FirmwareForm;
  }
  export interface UpdateFirmwareParams {
    modelId: number;
    body?: FirmwareForm;
  }
  export interface GetFirmwareByIdAndModelIdParams {
    modelId: number;
    firmwareId: number;
  }
  export interface DeleteFirmwareParams {
    modelId: number;
    firmwareId: number;
  }
  export interface ListDevicesParams {
    page?: number;
    limit?: number;
  }
  export interface AssignCompanyToDeviceParams {
    deviceId: number;
    companyId: number;
  }
  export interface DeleteAssignCompanyToDeviceParams {
    deviceId: number;
    companyId: number;
  }
}
