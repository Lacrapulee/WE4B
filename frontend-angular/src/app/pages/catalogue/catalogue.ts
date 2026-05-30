import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { FormBuilder, FormGroup, ReactiveFormsModule } from '@angular/forms';
import { HttpClient } from '@angular/common/http'; // Import allégé
import { ArticleComponent } from './article';
import { CatalogueFilterComponent } from './catalogue-filter';

@Component({
  selector: 'app-catalogue',
  standalone: true,
  // On a enlevé HttpClientModule des imports car provideHttpClient() s'en occupe
  imports: [CommonModule, RouterModule, ReactiveFormsModule, ArticleComponent, CatalogueFilterComponent], 
  templateUrl: './catalogue.html'
})
export class CatalogueComponent implements OnInit {
  filterForm!: FormGroup;
  categories: any[] = [];
  items: any[] | null = [];
  
  isLoggedIn: boolean = true;
  userId: number = 1;

  constructor(private fb: FormBuilder, private http: HttpClient) {}

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
    this.loadProducts();
  }

  loadCategories() {
    this.http.get<any[]>('http://localhost:8000/api/categories.php')
      .subscribe({
        next: (data) => this.categories = data,
        error: (err) => console.error('Erreur cat:', err)
      });
  }

  loadProducts(params = {}) {
    this.http.get<any[]>('http://localhost:8000/api/catalogue.php', { params })
      .subscribe({
        next: (data) => this.items = data,
        error: (err) => console.error('Erreur produits:', err)
      });
  }

  applyFilters(params: any = null) {
    const query = params ?? this.filterForm.value;
    this.loadProducts(query);
  }

  toggleFavoris(item: any) {
    this.http.post('http://localhost:8000/api/favoris.php', { article_id: item.id })
      .subscribe((res: any) => {
        if (res.success) {
          item.isFavoris = !item.isFavoris;
        }
      });
  }
}