import { TestBed, async, inject } from '@angular/core/testing';

import { CleanupSocketGuard } from './cleanup-socket.guard';

describe('CleanupSocketGuard', () => {
  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [CleanupSocketGuard]
    });
  });

  it('should ...', inject([CleanupSocketGuard], (guard: CleanupSocketGuard) => {
    expect(guard).toBeTruthy();
  }));
});
