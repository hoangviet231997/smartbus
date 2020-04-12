import { Injectable } from '@angular/core';
import { AdminActivityLogsService} from '../api/services';
import { ActivityLogFrom } from '../api/models';

@Injectable({
  providedIn: 'root'
})
export class ActivityLogsService {

  constructor( private apiActivityLogs: AdminActivityLogsService) { 
  }

  createActivityLog(data: ActivityLogFrom){
    this.apiActivityLogs.createActivityLog({
      user_down: data['user_down'] ? data['user_down'] : null,
      action: data['action'],
      subject_type: data['subject_type'],
      subject_data: data['subject_data']
    }).subscribe(
      res => {
        return res;
      }
    );
  }
}
