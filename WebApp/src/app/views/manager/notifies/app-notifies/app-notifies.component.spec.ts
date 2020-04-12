import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { NotifiesComponent } from './notifies.component';

describe('NotifiesComponent', () => {
  let component: NotifiesComponent;
  let fixture: ComponentFixture<NotifiesComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ NotifiesComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(NotifiesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
