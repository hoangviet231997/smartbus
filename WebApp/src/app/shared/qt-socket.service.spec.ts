import { TestBed, inject } from '@angular/core/testing';

import { QtSocketService } from './qt-socket.service';

describe('QtSocketService', () => {
  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [QtSocketService]
    });
  });

  it('should be created', inject([QtSocketService], (service: QtSocketService) => {
    expect(service).toBeTruthy();
  }));
});
