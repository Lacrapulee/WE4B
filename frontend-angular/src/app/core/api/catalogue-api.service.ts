import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { CatalogueCategory, CatalogueFilters, CatalogueItem } from '../models/catalogue.models';

@Injectable({ providedIn: 'root' })
export class CatalogueApiService {
  private readonly baseUrl = 'http://localhost:8000/api';

  constructor(private http: HttpClient) {}

  getCategories() {
    return this.http.get<CatalogueCategory[]>(`${this.baseUrl}/categories.php`);
  }

  getCatalogue(filters: CatalogueFilters = {}) {
    let params = new HttpParams();

    for (const [key, value] of Object.entries(filters)) {
      if (value !== undefined && value !== null && value !== '') {
        params = params.set(key, value);
      }
    }

    return this.http.get<CatalogueItem[]>(`${this.baseUrl}/catalogue.php`, { params });
  }

  getItem(id: number) {
    return this.http.get<CatalogueItem>(`${this.baseUrl}/catalogue.php`, { params: { id } as any });
  }

  toggleFavoris(articleId: number) {
    return this.http.post<{ success: boolean }>(`${this.baseUrl}/favoris.php`, { article_id: articleId });
  }
}