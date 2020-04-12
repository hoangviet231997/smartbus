import { Injectable } from '@angular/core';
import { interval, Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { webSocket, RxSocketClientSubject } from '@akanass/rx-socket-client';
import swal from 'sweetalert2';


// const SERVER_URL = 'ws://localhost:9876';

@Injectable({
  providedIn: 'root'
})
export class QtSocketService {
  private scannerTimer: Observable<number>;
  private socket$: RxSocketClientSubject<{}>;
  private connected = false;

  socket() {
    // return this.socket$;
  }

  onData() {
    // return this.socket$.onBytes$();
  }

  constructor() {
    // console.log('QtSocket constructed.');

    // this.socket$ = webSocket({
    //   url: SERVER_URL,
    //   reconnectInterval: 4000,
    //   reconnectAttempts: 999999
    // });

    // this.socket$.onBytes$().subscribe(data => {
    //   // swal(data.toString());
    //   console.log('from qt socket:', data.toString());
    // });

    // this.socket$.connectionStatus$.subscribe(
    //   isConnected => {
    //     if (isConnected) {
    //       console.log('Server connected');
    //       this.connected = true;
    //     } else {
    //       this.connected = false;
    //       console.log('Server disconnected');
    //     }
    //   }
    // );

    // this.socket$.on('close', () => console.log('Socket closed'));

    // this.scannerTimer = interval(5000);
    // this.scannerTimer.subscribe(
    //   i => {
    //     // console.log('interval=', i);
    //     if (!this.connected) {
    //     }
    //   }
    // );
  }
}
