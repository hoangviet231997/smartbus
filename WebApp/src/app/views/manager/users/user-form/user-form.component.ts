import { Component, OnInit, AfterViewInit } from '@angular/core';
import { QtSocketService } from '../../../../shared/qt-socket.service';
import { Observable } from 'rxjs';
import { WebSocketBinaryServer } from '@akanass/rx-socket-client';

@Component({
  selector: 'app-user-form',
  templateUrl: './user-form.component.html',
  styleUrls: ['./user-form.component.css']
})
export class UserFormComponent implements OnInit, AfterViewInit {
  private socketSubscription: any;

  constructor(private qtSocket: QtSocketService) { }

  ngOnInit() {

  }

  ngAfterViewInit() {
    // this.socketSubscription = this.qtSocket.onData().subscribe(
    //   data => {

    //   }
    // );
  }

}
