import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { DecodeOnlinesComponent } from './decode-onlines.component';

describe('DecodeOnlinesComponent', () => {
  let component: DecodeOnlinesComponent;
  let fixture: ComponentFixture<DecodeOnlinesComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ DecodeOnlinesComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(DecodeOnlinesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
