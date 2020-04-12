import { TestBed, async, inject } from '@angular/core/testing';

import { RoleAdminGuard } from './role-admin.guard';

describe('RoleAdminGuard', () => {
  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [RoleAdminGuard]
    });
  });

  it('should ...', inject([RoleAdminGuard], (guard: RoleAdminGuard) => {
    expect(guard).toBeTruthy();
  }));
});
