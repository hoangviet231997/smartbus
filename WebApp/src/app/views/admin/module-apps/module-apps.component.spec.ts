import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ModuleAppsComponent } from './module-apps.component';

describe('ModuleAppsComponent', () => {
  let component: ModuleAppsComponent;
  let fixture: ComponentFixture<ModuleAppsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ModuleAppsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ModuleAppsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
