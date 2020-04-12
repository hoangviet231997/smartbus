import { Component, OnInit, AfterViewInit, ViewChild } from '@angular/core';
import { ManagerNotifiesService } from "../../../../api/services/manager-notifies.service";
import { TranslateService } from '@ngx-translate/core';
import swal from 'sweetalert2';
import { User } from '../../../../api/models';
import { NgxSpinnerService } from 'ngx-spinner';
import { Router } from '@angular/router';
import { AppHeaderComponent } from '../../../../shared/app-header/app-header.component'
import { map } from 'rxjs/operators/map';

@Component({
  selector: 'app-web-notifies',
  templateUrl: './web-notifies.component.html',
  styleUrls: ['./web-notifies.component.css']
})
export class WebNotifiesComponent implements OnInit, AfterViewInit {

  @ViewChild('appHeaderComponent') appHeaderComponent: AppHeaderComponent;

  public notifies: any = [];
  public limitPage = 20;

  //pagination
  public currentPage = 1;
  public paginationTotal;
  public paginationCurrent;
  public paginationLast;

  public permissions: any[] = [];
  public style_search: any = '';
  public key_input: any = '';
  public timeoutSearchNotify: any;
  public maxDate: Date

  constructor(
    private apiNotify: ManagerNotifiesService,
    private translate: TranslateService,
    private spinner: NgxSpinnerService,
    private router: Router,
  ){
    this.maxDate = new Date();
  }

  ngOnInit() {
    if (localStorage.getItem('user')) this.permissions = JSON.parse(localStorage.getItem('user')).permissions;
  }

  ngAfterViewInit() {
    this.getNotify();
    this.style_search = '';
    this.key_input = '';
  }

  getNotify() {
    this.spinner.show();
    this.apiNotify.managerListNotifiesResponse({
      page: this.currentPage,
      limit: this.limitPage
    }).subscribe((data) => {
      this.notifies = [];
      data.body.forEach(element => {
        var subject_data = JSON.parse(element.subject_data)
        var obj = {
          id : element.id,
          title : element.title,
          subject_id : element.subject_id,
          subject_data : JSON.parse(element.subject_data),
          created_at: element.created_at,
          color : element.readed == 0 ? '#e0eef9' : '#fff',
          avatar: subject_data.avatar,
          key: element['key'],
          url_img: element['url_img'],
          route_link: element['route_link'],
          readed : element['readed']
        };
        if(element['key'] == 'mbs_expired'){
          if(this.permissions['card_membership_card']){
            this.notifies.push(obj);
          }
        }
        if(element['key'] == 'mbs_register'){
          if(this.permissions['card_membership_tmp']){
            this.notifies.push(obj);
          }
        }
      });

      this.paginationTotal = data.headers.get('pagination-total');
      this.paginationCurrent = data.headers.get('pagination-current');
      this.paginationLast = data.headers.get('pagination-last');
      this.spinner.hide();
    })
  }

  pageChanged(event: any): void {
    this.currentPage = event.page;
    this.getNotify();
  }

  deleteWebNotify(id: number) {
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
        this.apiNotify.managerDeleteNotify(id).subscribe((resp) => {
          swal(this.translate.instant('SWAL_DEL_SUCCESS'), '', 'success');
          this.getNotify();
          this.appHeaderComponent.getDataNotifies();
        });
      }
    });
  }

  gotoNotifyByRouteLink(obj: any) {

    switch (obj.key) {

      case "mbs_expired":

        if(this.permissions['card_membership_card']){
          if (obj.readed === 1) {
            this.router.navigate(['/'+obj.route_link], {queryParams: {subjectId: obj.subject_id}});
          } else {
              this.spinner.show();
              this.apiNotify.managerUpdateReadedNotify({
                id: obj.id,
                readed: 1
              }).subscribe(data => {
                this.spinner.hide();
                this.ngAfterViewInit();
                this.router.navigate(['/'+obj.route_link], {queryParams: {subjectId: obj.subject_id}});
              });
          }
        }else{
          swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_NOTIFY_PERMISION'), 'warning');
          return;
        }
        break;

      case 'mbs_register':

        if(this.permissions['card_membership_tmp']){

          if (obj.readed === 1) {
            this.router.navigate(['/'+obj.route_link], {queryParams: {subjectId: obj.subject_id}});
          } else {
            this.spinner.show();
            this.apiNotify.managerUpdateReadedNotify({
              id: obj.id,
              readed: 1
            }).subscribe(data => {
              this.spinner.hide();
              this.ngAfterViewInit();
              this.router.navigate(['/' + obj.route_link], { queryParams: { subjectId: obj.subject_id } });
            });
          }
        }else{
          swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_NOTIFY_PERMISION'), 'warning');
          return;
        }
        break;

      default:
        this.router.navigate(['/'+obj.route_link], {queryParams: {subjectId: obj.subject_id}});
        break;
    }
  }

  getDataSearchNotify() {

    clearTimeout(this.timeoutSearchNotify);
    if (this.style_search == '') {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_NOTYFY_SEARCH_ACTIVED'), 'warning');
      return;
    }

    this.timeoutSearchNotify = setTimeout(() => {
      if (this.key_input !== '') {
        this.spinner.show();
        this.apiNotify.managerListNotifyByInputAndByTypeSearch({
          style_search: this.style_search,
          key_input: this.key_input
        }).pipe(
          map(_r => {
            return _r;
          })
        ).subscribe(data => {
          this.notifies = [];
          if (data.length > 0) {
            data.forEach(element => {
              if(element['key'] == 'mbs_expired'){
                if(this.permissions['card_membership_card']){
                  this.notifies.push(element);
                }
              }
              if(element['key'] == 'mbs_register'){
                if(this.permissions['card_membership_tmp']){
                  this.notifies.push(element);
                }
              }
            });
          }
          this.spinner.hide();
        });
      } else {
        this.getNotify();
      }
    }, 500);
  }

}
