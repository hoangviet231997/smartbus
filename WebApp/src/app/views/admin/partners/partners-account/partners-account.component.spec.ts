import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { PartnersAccountComponent } from './partners-account.component';

describe('PartnersAccountComponent', () => {
  let component: PartnersAccountComponent;
  let fixture: ComponentFixture<PartnersAccountComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ PartnersAccountComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(PartnersAccountComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
