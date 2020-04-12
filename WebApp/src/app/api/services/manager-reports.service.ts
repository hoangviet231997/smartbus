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

import { ReceiptView } from '../models/receipt-view';
import { ReceiptForm } from '../models/receipt-form';
import { ReceiptAllForm } from '../models/receipt-all-form';
import { UpdateShiftForm } from '../models/update-shift-form';
import { ReceiptDetail } from '../models/receipt-detail';
import { RpTicketDestroyForm } from '../models/rp-ticket-destroy-form';
import { TicketDestroyView } from '../models/ticket-destroy-view';
import { TicketDestroyForm } from '../models/ticket-destroy-form';
import { inline_response_200 } from '../models/inline-_response-_200';
import { TicketDestroyAccept } from '../models/ticket-destroy-accept';
import { RpShiftDestroyForm } from '../models/rp-shift-destroy-form';
import { ShiftDestroyView } from '../models/shift-destroy-view';
import { ShiftDestroyForm } from '../models/shift-destroy-form';
import { ShiftDestroyAccept } from '../models/shift-destroy-accept';
import { RpStaffForm } from '../models/rp-staff-form';
import { StaffView } from '../models/staff-view';
import { RpDailyForm } from '../models/rp-daily-form';
import { DailyView } from '../models/daily-view';
import { RpVehicleForm } from '../models/rp-vehicle-form';
import { VehicleView } from '../models/vehicle-view';
import { RpVehicleAllForm } from '../models/rp-vehicle-all-form';
import { VehicleAllView } from '../models/vehicle-all-view';
import { RpTicketsForm } from '../models/rp-tickets-form';
import { TicketPrint } from '../models/ticket-print';
import { TicketView } from '../models/ticket-view';
import { CardView } from '../models/card-view';
import { RpCardForm } from '../models/rp-card-form';
import { CardMonthGeneralView } from '../models/card-month-general-view';
import { CardMonthGeneralForm } from '../models/card-month-general-form';
import { CardMonthRevenueView } from '../models/card-month-revenue-view';
import { CardMonthRevenueForm } from '../models/card-month-revenue-form';
import { CardMonthStaffView } from '../models/card-month-staff-view';
import { CardMonthStaffForm } from '../models/card-month-staff-form';
import { CardMonthGroupBusStationView } from '../models/card-month-group-bus-station-view';
import { CardMonthGroupBusStationForm } from '../models/card-month-group-bus-station-form';
import { CardExemption } from '../models/card-exemption';
import { CardExemptionForm } from '../models/card-exemption-form';
import { RpInvoiceForm } from '../models/rp-invoice-form';
import { NumberConvert } from '../models/number-convert';
import { TransactionDetailSearch } from '../models/transaction-detail-search';
import { TransactionOnline } from '../models/transaction-online';
import { RpTripView } from '../models/rp-trip-view';
import { RpTripForm } from '../models/rp-trip-form';
import { RpTimeKeepingForm } from '../models/rp-time-keeping-form';
import { RpOutputView } from '../models/rp-output-view';
import { RpOutputForm } from '../models/rp-output-form';
import { ShiftSupervisorView } from '../models/shift-supervisor-view';
import { ShiftSupervisorForm } from '../models/shift-supervisor-form';
import { VehicleRoutePeriodView } from '../models/vehicle-route-period-view';
import { VehicleRoutePeriodForm } from '../models/vehicle-route-period-form';


@Injectable()
export class ManagerReportsService extends BaseService {
  constructor(
    config: ApiConfiguration,
    http: HttpClient
  ) {
    super(config, http);
  }

  /**
   * @param body - undefined
   */
  managerReportsViewReceiptResponse(body?: ReceiptForm): Observable<HttpResponse<ReceiptView[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/receipt/view`,
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
        let _body: ReceiptView[] = null;
        _body = _resp.body as ReceiptView[]
        return _resp.clone({body: _body}) as HttpResponse<ReceiptView[]>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerReportsViewReceipt(body?: ReceiptForm): Observable<ReceiptView[]> {
    return this.managerReportsViewReceiptResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsViewAllReceiptResponse(body?: ReceiptAllForm): Observable<HttpResponse<ReceiptView[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/receipt/viewall`,
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
        let _body: ReceiptView[] = null;
        _body = _resp.body as ReceiptView[]
        return _resp.clone({body: _body}) as HttpResponse<ReceiptView[]>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerReportsViewAllReceipt(body?: ReceiptAllForm): Observable<ReceiptView[]> {
    return this.managerReportsViewAllReceiptResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsViewNotCollectMoneyReceiptResponse(body?: ReceiptAllForm): Observable<HttpResponse<ReceiptView[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/receipt/viewnotcollectmoney`,
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
        let _body: ReceiptView[] = null;
        _body = _resp.body as ReceiptView[]
        return _resp.clone({body: _body}) as HttpResponse<ReceiptView[]>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerReportsViewNotCollectMoneyReceipt(body?: ReceiptAllForm): Observable<ReceiptView[]> {
    return this.managerReportsViewNotCollectMoneyReceiptResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsExportReceiptResponse(body?: ReceiptForm): Observable<HttpResponse<string>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/receipt/export`,
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
   * @param body - undefined
   */
  managerReportsExportReceipt(body?: ReceiptForm): Observable<string> {
    return this.managerReportsExportReceiptResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  updateShiftsResponse(body?: UpdateShiftForm): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "PATCH",
      this.rootUrl + `/manager/reports/receipt/shifts`,
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
  updateShifts(body?: UpdateShiftForm): Observable<void> {
    return this.updateShiftsResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param shiftId - undefined
   */
  managerReportsExportReceiptTransactionByShiftIdResponse(shiftId: number): Observable<HttpResponse<string>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/receipt/${shiftId}/transaction`,
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
   * @param shiftId - undefined
   */
  managerReportsExportReceiptTransactionByShiftId(shiftId: number): Observable<string> {
    return this.managerReportsExportReceiptTransactionByShiftIdResponse(shiftId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param shiftId - undefined
   */
  managerReportsGetReceiptDetailByShiftIdResponse(shiftId: number): Observable<HttpResponse<ReceiptDetail>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/reports/receipt/shift/${shiftId}`,
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
        let _body: ReceiptDetail = null;
        _body = _resp.body as ReceiptDetail
        return _resp.clone({body: _body}) as HttpResponse<ReceiptDetail>;
      })
    );
  }

  /**
   * @param shiftId - undefined
   */
  managerReportsGetReceiptDetailByShiftId(shiftId: number): Observable<ReceiptDetail> {
    return this.managerReportsGetReceiptDetailByShiftIdResponse(shiftId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsAddTicketDestroyInTransactionResponse(body?: RpTicketDestroyForm): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/receipt/shift/transaction/ticketDestroy`,
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
  managerReportsAddTicketDestroyInTransaction(body?: RpTicketDestroyForm): Observable<void> {
    return this.managerReportsAddTicketDestroyInTransactionResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsViewTicketDestroyResponse(body?: TicketDestroyForm): Observable<HttpResponse<TicketDestroyView[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/receipt/shift/transaction/ticketDestroy/view`,
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
        let _body: TicketDestroyView[] = null;
        _body = _resp.body as TicketDestroyView[]
        return _resp.clone({body: _body}) as HttpResponse<TicketDestroyView[]>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerReportsViewTicketDestroy(body?: TicketDestroyForm): Observable<TicketDestroyView[]> {
    return this.managerReportsViewTicketDestroyResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsAcceptTicketDestroyResponse(body?: TicketDestroyAccept): Observable<HttpResponse<inline_response_200>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "DELETE",
      this.rootUrl + `/manager/reports/receipt/shift/transaction/ticketDestroy/accept`,
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
        let _body: inline_response_200 = null;
        _body = _resp.body as inline_response_200
        return _resp.clone({body: _body}) as HttpResponse<inline_response_200>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerReportsAcceptTicketDestroy(body?: TicketDestroyAccept): Observable<inline_response_200> {
    return this.managerReportsAcceptTicketDestroyResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsAddShiftDestroyResponse(body?: RpShiftDestroyForm): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/receipt/shift/shiftDestroys`,
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
  managerReportsAddShiftDestroy(body?: RpShiftDestroyForm): Observable<void> {
    return this.managerReportsAddShiftDestroyResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsViewShiftDestroysResponse(body?: ShiftDestroyForm): Observable<HttpResponse<ShiftDestroyView[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/receipt/shift/shiftDestroys/view`,
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
        let _body: ShiftDestroyView[] = null;
        _body = _resp.body as ShiftDestroyView[]
        return _resp.clone({body: _body}) as HttpResponse<ShiftDestroyView[]>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerReportsViewShiftDestroys(body?: ShiftDestroyForm): Observable<ShiftDestroyView[]> {
    return this.managerReportsViewShiftDestroysResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsAcceptShiftDestroyResponse(body?: ShiftDestroyAccept): Observable<HttpResponse<inline_response_200>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "DELETE",
      this.rootUrl + `/manager/reports/receipt/shift/shiftDestroys/accept`,
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
        let _body: inline_response_200 = null;
        _body = _resp.body as inline_response_200
        return _resp.clone({body: _body}) as HttpResponse<inline_response_200>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerReportsAcceptShiftDestroy(body?: ShiftDestroyAccept): Observable<inline_response_200> {
    return this.managerReportsAcceptShiftDestroyResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsExportStaffResponse(body?: RpStaffForm): Observable<HttpResponse<string>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/staff/export`,
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
   * @param body - undefined
   */
  managerReportsExportStaff(body?: RpStaffForm): Observable<string> {
    return this.managerReportsExportStaffResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsViewStaffResponse(body?: RpStaffForm): Observable<HttpResponse<StaffView[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/staff/view`,
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
        let _body: StaffView[] = null;
        _body = _resp.body as StaffView[]
        return _resp.clone({body: _body}) as HttpResponse<StaffView[]>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerReportsViewStaff(body?: RpStaffForm): Observable<StaffView[]> {
    return this.managerReportsViewStaffResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsExportDailyResponse(body?: RpDailyForm): Observable<HttpResponse<string>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/daily/export`,
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
   * @param body - undefined
   */
  managerReportsExportDaily(body?: RpDailyForm): Observable<string> {
    return this.managerReportsExportDailyResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsViewDailyResponse(body?: RpDailyForm): Observable<HttpResponse<DailyView[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/daily/view`,
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
        let _body: DailyView[] = null;
        _body = _resp.body as DailyView[]
        return _resp.clone({body: _body}) as HttpResponse<DailyView[]>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerReportsViewDaily(body?: RpDailyForm): Observable<DailyView[]> {
    return this.managerReportsViewDailyResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsExportVehiclesResponse(body?: RpVehicleForm): Observable<HttpResponse<string>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/vehicles/export`,
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
   * @param body - undefined
   */
  managerReportsExportVehicles(body?: RpVehicleForm): Observable<string> {
    return this.managerReportsExportVehiclesResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsViewVehiclesResponse(body?: RpVehicleForm): Observable<HttpResponse<VehicleView[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/vehicles/view`,
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
        let _body: VehicleView[] = null;
        _body = _resp.body as VehicleView[]
        return _resp.clone({body: _body}) as HttpResponse<VehicleView[]>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerReportsViewVehicles(body?: RpVehicleForm): Observable<VehicleView[]> {
    return this.managerReportsViewVehiclesResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsExportVehicleAllResponse(body?: RpVehicleAllForm): Observable<HttpResponse<string>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/vehicleall/export`,
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
   * @param body - undefined
   */
  managerReportsExportVehicleAll(body?: RpVehicleAllForm): Observable<string> {
    return this.managerReportsExportVehicleAllResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsViewVehicleAllResponse(body?: RpVehicleAllForm): Observable<HttpResponse<VehicleAllView[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/vehicleall/view`,
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
        let _body: VehicleAllView[] = null;
        _body = _resp.body as VehicleAllView[]
        return _resp.clone({body: _body}) as HttpResponse<VehicleAllView[]>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerReportsViewVehicleAll(body?: RpVehicleAllForm): Observable<VehicleAllView[]> {
    return this.managerReportsViewVehicleAllResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsExportVehicleByPeriodResponse(body?: RpVehicleAllForm): Observable<HttpResponse<string>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/vehicleperiod/export`,
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
   * @param body - undefined
   */
  managerReportsExportVehicleByPeriod(body?: RpVehicleAllForm): Observable<string> {
    return this.managerReportsExportVehicleByPeriodResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsViewVehicleByPeriodResponse(body?: RpVehicleAllForm): Observable<HttpResponse<VehicleAllView[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/vehicleperiod/view`,
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
        let _body: VehicleAllView[] = null;
        _body = _resp.body as VehicleAllView[]
        return _resp.clone({body: _body}) as HttpResponse<VehicleAllView[]>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerReportsViewVehicleByPeriod(body?: RpVehicleAllForm): Observable<VehicleAllView[]> {
    return this.managerReportsViewVehicleByPeriodResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsExportTicketsResponse(body?: RpTicketsForm): Observable<HttpResponse<string>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/tickets/export`,
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
   * @param body - undefined
   */
  managerReportsExportTickets(body?: RpTicketsForm): Observable<string> {
    return this.managerReportsExportTicketsResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsExportTicketsByStationResponse(body?: RpTicketsForm): Observable<HttpResponse<string>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/tickets/exportTicketByStation`,
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
   * @param body - undefined
   */
  managerReportsExportTicketsByStation(body?: RpTicketsForm): Observable<string> {
    return this.managerReportsExportTicketsByStationResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsPrintTicketsResponse(body?: RpTicketsForm): Observable<HttpResponse<TicketPrint[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/tickets/print`,
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
        let _body: TicketPrint[] = null;
        _body = _resp.body as TicketPrint[]
        return _resp.clone({body: _body}) as HttpResponse<TicketPrint[]>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerReportsPrintTickets(body?: RpTicketsForm): Observable<TicketPrint[]> {
    return this.managerReportsPrintTicketsResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsViewTicketsResponse(body?: RpTicketsForm): Observable<HttpResponse<TicketView[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/tickets/view`,
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
        let _body: TicketView[] = null;
        _body = _resp.body as TicketView[]
        return _resp.clone({body: _body}) as HttpResponse<TicketView[]>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerReportsViewTickets(body?: RpTicketsForm): Observable<TicketView[]> {
    return this.managerReportsViewTicketsResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsViewTicketsByStationResponse(body?: RpTicketsForm): Observable<HttpResponse<TicketView[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/tickets/viewByStation`,
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
        let _body: TicketView[] = null;
        _body = _resp.body as TicketView[]
        return _resp.clone({body: _body}) as HttpResponse<TicketView[]>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerReportsViewTicketsByStation(body?: RpTicketsForm): Observable<TicketView[]> {
    return this.managerReportsViewTicketsByStationResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsViewCardResponse(body?: RpCardForm): Observable<HttpResponse<CardView[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/card/view`,
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
        let _body: CardView[] = null;
        _body = _resp.body as CardView[]
        return _resp.clone({body: _body}) as HttpResponse<CardView[]>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerReportsViewCard(body?: RpCardForm): Observable<CardView[]> {
    return this.managerReportsViewCardResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsExportCardResponse(body?: RpCardForm): Observable<HttpResponse<string>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/card/export`,
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
   * @param body - undefined
   */
  managerReportsExportCard(body?: RpCardForm): Observable<string> {
    return this.managerReportsExportCardResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsViewCardMonthForGeneralResponse(body?: CardMonthGeneralForm): Observable<HttpResponse<CardMonthGeneralView[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/cardMonth/general/view`,
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
        let _body: CardMonthGeneralView[] = null;
        _body = _resp.body as CardMonthGeneralView[]
        return _resp.clone({body: _body}) as HttpResponse<CardMonthGeneralView[]>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerReportsViewCardMonthForGeneral(body?: CardMonthGeneralForm): Observable<CardMonthGeneralView[]> {
    return this.managerReportsViewCardMonthForGeneralResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsExportCardMonthForGeneralResponse(body?: CardMonthGeneralForm): Observable<HttpResponse<string>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/cardMonth/general/export`,
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
   * @param body - undefined
   */
  managerReportsExportCardMonthForGeneral(body?: CardMonthGeneralForm): Observable<string> {
    return this.managerReportsExportCardMonthForGeneralResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsViewCardMonthRevenueResponse(body?: CardMonthRevenueForm): Observable<HttpResponse<CardMonthRevenueView[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/cardMonth/revenue/view`,
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
        let _body: CardMonthRevenueView[] = null;
        _body = _resp.body as CardMonthRevenueView[]
        return _resp.clone({body: _body}) as HttpResponse<CardMonthRevenueView[]>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerReportsViewCardMonthRevenue(body?: CardMonthRevenueForm): Observable<CardMonthRevenueView[]> {
    return this.managerReportsViewCardMonthRevenueResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsExportCardMonthRevenueResponse(body?: CardMonthRevenueForm): Observable<HttpResponse<string>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/cardMonth/revenue/export`,
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
   * @param body - undefined
   */
  managerReportsExportCardMonthRevenue(body?: CardMonthRevenueForm): Observable<string> {
    return this.managerReportsExportCardMonthRevenueResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsViewCardMonthByStaffResponse(body?: CardMonthStaffForm): Observable<HttpResponse<CardMonthStaffView[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/cardMonth/staff/view`,
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
        let _body: CardMonthStaffView[] = null;
        _body = _resp.body as CardMonthStaffView[]
        return _resp.clone({body: _body}) as HttpResponse<CardMonthStaffView[]>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerReportsViewCardMonthByStaff(body?: CardMonthStaffForm): Observable<CardMonthStaffView[]> {
    return this.managerReportsViewCardMonthByStaffResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsExportCardMonthByStaffResponse(body?: CardMonthStaffForm): Observable<HttpResponse<string>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/cardMonth/staff/export`,
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
   * @param body - undefined
   */
  managerReportsExportCardMonthByStaff(body?: CardMonthStaffForm): Observable<string> {
    return this.managerReportsExportCardMonthByStaffResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsViewCardMonthByGroupBusStationResponse(body?: CardMonthGroupBusStationForm): Observable<HttpResponse<CardMonthGroupBusStationView[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/cardMonthGroupBusStation/view`,
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
        let _body: CardMonthGroupBusStationView[] = null;
        _body = _resp.body as CardMonthGroupBusStationView[]
        return _resp.clone({body: _body}) as HttpResponse<CardMonthGroupBusStationView[]>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerReportsViewCardMonthByGroupBusStation(body?: CardMonthGroupBusStationForm): Observable<CardMonthGroupBusStationView[]> {
    return this.managerReportsViewCardMonthByGroupBusStationResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsExportCardMonthByGroupBusStationResponse(body?: CardMonthGroupBusStationForm): Observable<HttpResponse<string>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/cardMonthGroupBusStation/export`,
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
   * @param body - undefined
   */
  managerReportsExportCardMonthByGroupBusStation(body?: CardMonthGroupBusStationForm): Observable<string> {
    return this.managerReportsExportCardMonthByGroupBusStationResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsViewCardExemptionResponse(body?: CardExemptionForm): Observable<HttpResponse<CardExemption[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/cardExemption/view`,
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
        let _body: CardExemption[] = null;
        _body = _resp.body as CardExemption[]
        return _resp.clone({body: _body}) as HttpResponse<CardExemption[]>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerReportsViewCardExemption(body?: CardExemptionForm): Observable<CardExemption[]> {
    return this.managerReportsViewCardExemptionResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsExportCardExemptionResponse(body?: CardExemptionForm): Observable<HttpResponse<string>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/cardExemption/export`,
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
   * @param body - undefined
   */
  managerReportsExportCardExemption(body?: CardExemptionForm): Observable<string> {
    return this.managerReportsExportCardExemptionResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsExportInvoicesResponse(body?: RpInvoiceForm): Observable<HttpResponse<string>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/invoices/export`,
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
   * @param body - undefined
   */
  managerReportsExportInvoices(body?: RpInvoiceForm): Observable<string> {
    return this.managerReportsExportInvoicesResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsConvertNumberToStringResponse(body?: NumberConvert): Observable<HttpResponse<string>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/receipt/convert`,
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
   * @param body - undefined
   */
  managerReportsConvertNumberToString(body?: NumberConvert): Observable<string> {
    return this.managerReportsConvertNumberToStringResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerTransactionDetailSearchResponse(body?: TransactionDetailSearch): Observable<HttpResponse<ReceiptDetail[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/transactiondetail/search`,
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
        let _body: ReceiptDetail[] = null;
        _body = _resp.body as ReceiptDetail[]
        return _resp.clone({body: _body}) as HttpResponse<ReceiptDetail[]>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerTransactionDetailSearch(body?: TransactionDetailSearch): Observable<ReceiptDetail[]> {
    return this.managerTransactionDetailSearchResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsViewTransactionOnlineResponse(body?: TransactionOnline): Observable<HttpResponse<ReceiptDetail[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/transaction/online/view`,
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
        let _body: ReceiptDetail[] = null;
        _body = _resp.body as ReceiptDetail[]
        return _resp.clone({body: _body}) as HttpResponse<ReceiptDetail[]>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerReportsViewTransactionOnline(body?: TransactionOnline): Observable<ReceiptDetail[]> {
    return this.managerReportsViewTransactionOnlineResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsExportTransactionOnlineResponse(body?: TransactionOnline): Observable<HttpResponse<string>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/transaction/online/export`,
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
   * @param body - undefined
   */
  managerReportsExportTransactionOnline(body?: TransactionOnline): Observable<string> {
    return this.managerReportsExportTransactionOnlineResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerTransactionDetailReportResponse(body?: TransactionDetailSearch): Observable<HttpResponse<string>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/transactiondetail/export`,
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
   * @param body - undefined
   */
  managerTransactionDetailReport(body?: TransactionDetailSearch): Observable<string> {
    return this.managerTransactionDetailReportResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsViewTripResponse(body?: RpTripForm): Observable<HttpResponse<RpTripView[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/trip/view`,
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
        let _body: RpTripView[] = null;
        _body = _resp.body as RpTripView[]
        return _resp.clone({body: _body}) as HttpResponse<RpTripView[]>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerReportsViewTrip(body?: RpTripForm): Observable<RpTripView[]> {
    return this.managerReportsViewTripResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsExportTripResponse(body?: RpTripForm): Observable<HttpResponse<string>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/trip/export`,
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
   * @param body - undefined
   */
  managerReportsExportTrip(body?: RpTripForm): Observable<string> {
    return this.managerReportsExportTripResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsExportTripTimeDetailResponse(body?: RpTripForm): Observable<HttpResponse<string>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/trip/detail/export`,
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
   * @param body - undefined
   */
  managerReportsExportTripTimeDetail(body?: RpTripForm): Observable<string> {
    return this.managerReportsExportTripTimeDetailResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsExportTimeKeepingResponse(body?: RpTimeKeepingForm): Observable<HttpResponse<string>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/timekeeping/export`,
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
   * @param body - undefined
   */
  managerReportsExportTimeKeeping(body?: RpTimeKeepingForm): Observable<string> {
    return this.managerReportsExportTimeKeepingResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsViewOutputByVehicleResponse(body?: RpOutputForm): Observable<HttpResponse<RpOutputView[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/outputVehicle/view`,
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
        let _body: RpOutputView[] = null;
        _body = _resp.body as RpOutputView[]
        return _resp.clone({body: _body}) as HttpResponse<RpOutputView[]>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerReportsViewOutputByVehicle(body?: RpOutputForm): Observable<RpOutputView[]> {
    return this.managerReportsViewOutputByVehicleResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsExportOutputByVehicleResponse(body?: RpOutputForm): Observable<HttpResponse<string>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/outputVehicle/export`,
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
   * @param body - undefined
   */
  managerReportsExportOutputByVehicle(body?: RpOutputForm): Observable<string> {
    return this.managerReportsExportOutputByVehicleResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsViewShiftSupervisorResponse(body?: ShiftSupervisorForm): Observable<HttpResponse<ShiftSupervisorView[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/shiftSupervisor/view`,
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
        let _body: ShiftSupervisorView[] = null;
        _body = _resp.body as ShiftSupervisorView[]
        return _resp.clone({body: _body}) as HttpResponse<ShiftSupervisorView[]>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerReportsViewShiftSupervisor(body?: ShiftSupervisorForm): Observable<ShiftSupervisorView[]> {
    return this.managerReportsViewShiftSupervisorResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsExportShiftSupervisorResponse(body?: ShiftSupervisorForm): Observable<HttpResponse<string>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/shiftSupervisor/export`,
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
   * @param body - undefined
   */
  managerReportsExportShiftSupervisor(body?: ShiftSupervisorForm): Observable<string> {
    return this.managerReportsExportShiftSupervisorResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsViewVehicleRoutePriodResponse(body?: VehicleRoutePeriodForm): Observable<HttpResponse<VehicleRoutePeriodView[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/vehicleroutepriod/view`,
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
        let _body: VehicleRoutePeriodView[] = null;
        _body = _resp.body as VehicleRoutePeriodView[]
        return _resp.clone({body: _body}) as HttpResponse<VehicleRoutePeriodView[]>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerReportsViewVehicleRoutePriod(body?: VehicleRoutePeriodForm): Observable<VehicleRoutePeriodView[]> {
    return this.managerReportsViewVehicleRoutePriodResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerReportsExportVehicleRoutePriodResponse(body?: VehicleRoutePeriodForm): Observable<HttpResponse<string>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/reports/vehicleroutepriod/export`,
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
   * @param body - undefined
   */
  managerReportsExportVehicleRoutePriod(body?: VehicleRoutePeriodForm): Observable<string> {
    return this.managerReportsExportVehicleRoutePriodResponse(body).pipe(
      map(_r => _r.body)
    );
  }}

export module ManagerReportsService {
}
