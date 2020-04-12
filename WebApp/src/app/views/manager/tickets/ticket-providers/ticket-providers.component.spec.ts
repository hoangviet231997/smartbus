import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { TicketProvidersComponent } from './ticket-providers.component';

describe('TicketProvidersComponent', () => {
  let component: TicketProvidersComponent;
  let fixture: ComponentFixture<TicketProvidersComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ TicketProvidersComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(TicketProvidersComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
