import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { WebNotifiesComponent } from './web-notifies.component';

describe('WebNotifiesComponent', () => {
  let component: WebNotifiesComponent;
  let fixture: ComponentFixture<WebNotifiesComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ WebNotifiesComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(WebNotifiesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
