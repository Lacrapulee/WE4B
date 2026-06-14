import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { FormBuilder, ReactiveFormsModule } from '@angular/forms';
import { ArticleComponent } from './components/article/article.component';
import { CatalogueFilterComponent } from './components/catalogue-filter/catalogue-filter.component';
import { CatalogueApiService } from '../../core/api/catalogue-api.service';
import { AuthService } from '../../core/api/auth.service';
import { CatalogueCategory, CatalogueFilters, CatalogueItem } from '../../core/models/catalogue.models';

@Component({
  selector: 'app-catalogue',
  standalone: true,
  imports: [CommonModule, RouterModule, ReactiveFormsModule, ArticleComponent, CatalogueFilterComponent],
  templateUrl: './catalogue.component.html',
  styleUrls: ['./catalogue.component.css']
})
export class CatalogueComponent implements OnInit {
  categories: CatalogueCategory[] = [];
  items: CatalogueItem[] = [];
  isLoggedIn: boolean = false;
  userId: string | number | null = null;

  constructor(
    private fb: FormBuilder,
    private api: CatalogueApiService,
    private authService: AuthService
  ) {}

  ngOnInit(): void {
    this.authService.currentUser$.subscribe(state => {
      this.isLoggedIn = state.isLoggedIn;
      this.userId = state.user_id || null;
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
    const query = params ?? {};  // plus de filterForm, le filtre vient du child component
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
          this.items = this.items.map(i =>
            i.id === item.id ? { ...i, isFavoris: !i.isFavoris } : i
          );
        }
      },
      error: (error) => console.error('Erreur favoris:', error)
    });
  }
}