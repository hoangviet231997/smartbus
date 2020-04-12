import { Component, OnInit, AfterViewInit } from '@angular/core';
import { Route } from '../../../../api/models/route';
import { ManagerRoutesService } from '../../../../api/services';
import { map } from 'rxjs/operators/map';
import { Router } from '@angular/router';
import { NgxSpinnerService } from 'ngx-spinner';
import swal from 'sweetalert2';
import { TranslateService } from '@ngx-translate/core';
import { ActivityLogsService } from '../../../../shared/activity-logs.service';

@Component({
  selector: 'app-routes-list',
  templateUrl: './routes-list.component.html',
  styleUrls: ['./routes-list.component.css']
})
export class RoutesListComponent implements OnInit, AfterViewInit {

  public routes: Route[];
  public isCreated = false;
  public isUpdated = false;

  public limitPage = 10;
  public currentPage = 1;
  public paginationTotal;
  public paginationCurrent;
  public paginationLast;

  public user_down: any = null;
  public permissions: any[] = [];

  public searchRoute: any = '';
  public timeoutSearchRoute;
  public style_search: any = '';
  public key_input: any = '';

  constructor(
    private apiRoutes: ManagerRoutesService,
    private router: Router,
    private spinner: NgxSpinnerService,
    private translate: TranslateService,
    private activityLogs: ActivityLogsService,
  ) { }

  ngOnInit() {

    this.user_down = localStorage.getItem('token_shadow');
    if (localStorage.getItem('user')) {
      this.permissions = JSON.parse(localStorage.getItem('user')).permissions;
    }

    this.user_down = localStorage.getItem('token_shadow');
  }

  ngAfterViewInit() {
    this.refreshView();
    this.style_search = '';
    this.key_input = '';
  }

  refreshView() {
    this.spinner.show();
    this.apiRoutes.managerlistRoutesResponse({
      page: this.currentPage,
      limit: this.limitPage
    }).pipe(
      map(_r => {
        return _r;
      })
    ).subscribe(
      resp => {

        this.routes = resp.body;

        this.paginationTotal = resp.headers.get('pagination-total');
        this.paginationCurrent = resp.headers.get('pagination-current');
        this.paginationLast = resp.headers.get('pagination-last');
          this.spinner.hide();
      }
    );
  }

  deleteRoute(id: number) {
    swal({
      title: this.translate.instant('SWAL_ERROR_SURE'),
      text: this.translate.instant('SWAL_ERROR_REMOVE'),
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: this.translate.instant('SWAL_OK'),
      cancelButtonText: this.translate.instant('SWAL_CANCEL')
    }).then((result) => {
      if (result.value) {
        this.spinner.show();
        this.apiRoutes.managerDeleteRoute(id).subscribe(
          res => {

            //call service create activity log
            var activity_log: any = [];
            activity_log['user_down'] =  this.user_down ? this.user_down : null;
            activity_log['action'] = 'delete';
            activity_log['subject_type'] = 'route';
            activity_log['subject_data'] = id ? JSON.stringify({id:id}) : '';
            var activityLog = this.activityLogs.createActivityLog(activity_log);

            this.refreshView();
            this.spinner.hide();
            swal(this.translate.instant('SWAL_DEL_SUCCESS'), '', 'success');
          },
          err => {
            this.spinner.hide();
            swal({ type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_DEL_FAILD') });
          }
        );
      }
    });
  }

  editRoute(id: number): void {
    this.router.navigate(['/manager/routes/routes-form'], { queryParams: { idRoute: id }, skipLocationChange: true });
  }

  pageChanged(event: any): void {
    this.currentPage = event.page;
    this.refreshView();
  }

  searchRouteByKey() {

    clearTimeout(this.timeoutSearchRoute);
    if (this.style_search == '') {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_ROUTE_SEARCH_ACTIVED'), 'warning');
      return;
    }

    this.routes = [];
    this.timeoutSearchRoute = setTimeout(() => {
      if (this.key_input !== '') {
        this.spinner.show();
        this.apiRoutes.managerSearchRoute({
          style_search: this.style_search,
          key_input: this.key_input
        }).subscribe(data => {
          if (data.length > 0) {
            this.routes = data;
          } else this.refreshView();
          this.spinner.hide();
        });
      } else {
        this.refreshView();
      }
    }, 500);
  }

}
