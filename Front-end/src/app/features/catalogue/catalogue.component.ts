import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { FormBuilder, FormGroup, ReactiveFormsModule } from '@angular/forms';
import { ArticleComponent } from './components/article/article.component';
import { CatalogueFilterComponent } from './components/catalogue-filter/catalogue-filter.component';
import { CatalogueApiService } from '../../core/api/catalogue-api.service';
import { CatalogueCategory, CatalogueFilters, CatalogueItem } from '../../core/models/catalogue.models';

@Component({
  selector: 'app-catalogue',
  standalone: true,
  imports: [CommonModule, RouterModule, ReactiveFormsModule, ArticleComponent, CatalogueFilterComponent],
  templateUrl: './catalogue.component.html',
  styleUrls: ['./catalogue.component.css']
})
export class CatalogueComponent implements OnInit {
  filterForm!: FormGroup;
  categories: CatalogueCategory[] = [];
  items: CatalogueItem[] = [];

  isLoggedIn: boolean = true;
  userId: number = 1;

  constructor(private fb: FormBuilder, private api: CatalogueApiService) {}

  ngOnInit(): void {
    this.filterForm = this.fb.group({
      search: [''],
      categorie: [''],
      ville: [''],
      distance: [''],
      prix_min: [''],
      prix_max: [''],
      tri: ['date_recent']
    });

    this.loadCategories();
    this.applyFilters();
  }

  loadCategories() {
    this.api.getCategories().subscribe({
      next: (data) => this.categories = data,
      error: (error) => console.error('Erreur cat:', error)
    });
  }

  applyFilters(params: CatalogueFilters | null = null) {
    const query = params ?? this.filterForm.value;

    this.api.getCatalogue(query).subscribe({
      next: (data) => this.items = data,
      error: (error) => console.error('Erreur produits:', error)
    });
  }

  toggleFavoris(item: CatalogueItem) {
    const action = item.isFavoris ? 'remove' : 'add';
    this.api.toggleFavoris(item.id, action).subscribe({
      next: (response) => {
        if (response.success) {
          item.isFavoris = !item.isFavoris;
        }
      },
      error: (error) => console.error('Erreur favoris:', error)
    });
  }
}