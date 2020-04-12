import { Component, OnInit } from '@angular/core';
import { ManagerLayoutService } from '../../api/services';


@Component({
  selector: 'app-manager-layout',
  templateUrl: './manager-layout.component.html',
  styleUrls: ['./manager-layout.component.css']
})
export class ManagerLayoutComponent implements OnInit {

  public permissions:any[] = [];
  public user_down: any = null ;

  constructor(private apiPermissions: ManagerLayoutService) {}

  ngOnInit() {

    this.user_down = localStorage.getItem('token_shadow');

    if (localStorage.getItem('user')) {
      this.permissions = JSON.parse(localStorage.getItem('user')).permissions;
    }
    // this.apiPermissions.listPermissionsByRoleAndCompanyId().subscribe(
    //   res => {
    //     this.permissions = res;
    //   }
    // );
  }

  ngAfterViewInit() {
    this.refreshView();
  }

  refreshView() {
  }
}
