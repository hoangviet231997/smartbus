import { MembershipsModule } from './memberships.module';

describe('MembershipsModule', () => {
  let membershipsModule: MembershipsModule;

  beforeEach(() => {
    membershipsModule = new MembershipsModule();
  });

  it('should create an instance', () => {
    expect(membershipsModule).toBeTruthy();
  });
});
