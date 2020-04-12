import { TestBed, inject } from '@angular/core/testing';

import { ActivityLogsService } from './activity-logs.service';

describe('ActivityLogsService', () => {
  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [ActivityLogsService]
    });
  });

  it('should be created', inject([ActivityLogsService], (service: ActivityLogsService) => {
    expect(service).toBeTruthy();
  }));
});
