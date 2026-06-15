import { Component, EventEmitter, Input, Output, OnInit, OnChanges, SimpleChanges } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormGroup } from '@angular/forms';

@Component({
  selector: 'app-catalogue-filter',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './catalogue-filter.component.html',
  styleUrls: ['./catalogue-filter.component.css']
})
export class CatalogueFilterComponent implements OnInit, OnChanges {
  @Input() initial: any = {};
  @Input() categories: any[] = [];
  @Output() apply = new EventEmitter<any>();

  filterForm!: FormGroup;

  constructor(private fb: FormBuilder) {}

  ngOnInit(): void {
    this.filterForm = this.fb.group({
      search: [this.initial.search || ''],
      categorie: [this.initial.categorie || ''],
      ville: [this.initial.ville || ''],
      distance: [this.initial.distance || ''],
      prix_min: [this.initial.prix_min || ''],
      prix_max: [this.initial.prix_max || ''],
      tri: [this.initial.tri || 'date_recent']
    });
  }

  ngOnChanges(changes: SimpleChanges): void {
    if (changes['initial'] && this.filterForm) {
      this.filterForm.patchValue({
        search: this.initial.search || '',
        categorie: this.initial.categorie || '',
        ville: this.initial.ville || '',
        distance: this.initial.distance || '',
        prix_min: this.initial.prix_min || '',
        prix_max: this.initial.prix_max || '',
        tri: this.initial.tri || 'date_recent'
      });
    }
  }

  onSubmit() {
    this.apply.emit(this.filterForm.value);
  }
}