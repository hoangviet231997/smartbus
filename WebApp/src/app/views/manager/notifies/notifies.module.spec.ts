import { NotifiesModule } from './notifies.module';

describe('NotifiesModule', () => {
  let notifiesModule: NotifiesModule;

  beforeEach(() => {
    notifiesModule = new NotifiesModule();
  });

  it('should create an instance', () => {
    expect(notifiesModule).toBeTruthy();
  });
});
