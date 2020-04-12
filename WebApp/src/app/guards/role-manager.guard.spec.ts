import { TestBed, async, inject } from '@angular/core/testing';

import { RoleManagerGuard } from './role-manager.guard';

describe('RoleManagerGuard', () => {
  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [RoleManagerGuard]
    });
  });

  it('should ...', inject([RoleManagerGuard], (guard: RoleManagerGuard) => {
    expect(guard).toBeTruthy();
  }));
});
